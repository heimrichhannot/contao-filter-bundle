<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Haste\Model\Relations;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\AdjustFilterValueEvent;
use HeimrichHannot\FilterBundle\Event\FilterQueryBuilderComposeEvent;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterQueryBuilder extends QueryBuilder
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var array
     */
    protected $contextualValues = [];

    /**
     * List of elements that should be skipped.
     *
     * @var FilterConfigElementModel[]
     */
    protected $skip = [];
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework, Connection $connection)
    {
        parent::__construct($connection);
        $this->framework = $framework;
        $this->container = $container;
    }

    /**
     * Add where clause based on an element.
     *
     * @param string $name The field name
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereElement(FilterConfigElementModel $element, string $name, FilterConfig $config, string $defaultOperator)
    {
        $filter = $config->getFilter();

        Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            return $this;
        }

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        if ($dca['eval']['isCategoryField'] ?? false) {
            $this->whereCategoryWidget($element, $name, $config, $dca, DatabaseUtil::OPERATOR_IN);

            return $this;
        }

        switch ($dca['inputType'] ?? null) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $this->whereTagWidget($element, $name, $config, $dca, DatabaseUtil::OPERATOR_IN);

                break;

            default:
                $this->whereWidget($element, $name, $config, $dca, $defaultOperator);
        }

        return $this;
    }

    /**
     * Add tag widget where clause.
     *
     * @param string $name The field name
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null)
    {
        $data = $config->getData();

        if (ChoiceType::TYPE == $element->type && \is_array($data[$name])) {
            $data[$name] = $this->getGroupChoiceValues($element, $data[$name]);
        }

        if ($element->isInitial) {
            $value = AbstractType::getInitialValue($element, $this->contextualValues);

            if ($element->alternativeValueSource) {
                $value = $this->getValueFromAlternativeSource($value, $data, $element, $name, $config, $dca);
            }

            if (!\in_array($element->operator, [DatabaseUtil::OPERATOR_IS_EMPTY, DatabaseUtil::OPERATOR_IS_NOT_EMPTY], true)
                && (null === $value
                    || !$element->field)) {
                return $this;
            }

            // never replace non initial Inserttags (user inputs), avoid injection and never cache to avoid esi:tags
            if (\is_array($value)) {
                foreach ($value as &$val) {
                    $val = Controller::replaceInsertTags($val, false);
                }
            } else {
                $value = Controller::replaceInsertTags($value, false);
            }

            $operator = $this->getOperator($element, $defaultOperator, $dca) ?: $defaultOperator;
        } else {
            $value = $data[$name] ?? ($element->customValue ? $element->value : null);

            $operator = $this->getOperator($element, $defaultOperator, $dca);
        }

        if (!$operator) {
            return $this;
        }

        /** @var FilterQueryBuilderComposeEvent $event */
        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new FilterQueryBuilderComposeEvent($this, $name, $operator, $value, $element, $config),
            FilterQueryBuilderComposeEvent::class
        );

        if (true === $event->getContinue() && !empty($event->getValue())) {
            $this->andWhere(
                $this->container->get('huh.utils.database')->composeWhereForQueryBuilder(
                    $this,
                    $config->getFilter()['dataContainer'].'.'.$element->field,
                    $event->getOperator(),
                    $dca,
                    $event->getValue(),
                    ['wildcardSuffix' => '_'.$element->id]
                )
            );
        }

        return $this;
    }

    public function addContextualValue($elementId, $value)
    {
        $this->contextualValues[$elementId] = $value;
    }

    public function getContextualValues(): array
    {
        return $this->contextualValues;
    }

    /**
     * Get filter skip elements.
     *
     * @return FilterConfigElementModel[]
     */
    public function getSkip()
    {
        return \is_array($this->skip) ? $this->skip : [];
    }

    /**
     * Set filter skip elements.
     *
     * @param FilterConfigElementModel[] $skip
     */
    public function setSkip(array $skip)
    {
        $this->skip = $skip;
    }

    /**
     * Add filter element to skip.
     */
    public function addSkip(FilterConfigElementModel $element)
    {
        $this->skip[$element->id] = $element;
    }

    protected function getOperator(FilterConfigElementModel $element, string $operator, array $dca, bool $supportSerializedBlob = true): string
    {
        if ((isset($dca['eval']['multiple']) && $dca['eval']['multiple'] || 'tagsinput' === ($dca['eval']['inputType'] ?? null)) && $supportSerializedBlob) {
            if (\in_array($operator, DatabaseUtil::NEGATIVE_OPERATORS)) {
                // db value is a serialized blob
                if (false !== strpos($dca['sql'], 'blob')) {
                    $operator = DatabaseUtil::OPERATOR_NOT_REGEXP;
                } else {
                    $operator = DatabaseUtil::OPERATOR_NOT_IN;
                }
            } else {
                if (false !== strpos($dca['sql'], 'blob')) {
                    $operator = DatabaseUtil::OPERATOR_REGEXP;
                } else {
                    $operator = DatabaseUtil::OPERATOR_IN;
                }
            }
        }

        if ($element->isInitial || $element->customOperator && $element->operator) {
            $operator = $element->operator;
        }

        return $operator;
    }

    /**
     * Add tag widget where clause.
     *
     * @param string $name            The field name
     * @param string $defaultOperator
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereTagWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null)
    {
        $data = $config->getData();

        if ($element->isInitial && $element->alternativeValueSource) {
            $value = $this->getValueFromAlternativeSource($data[$name], $data, $element, $name, $config, $dca);
        } else {
            $value = $data[$name] ?? AbstractType::getInitialValue($element, $this->contextualValues);
            $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);
        }

        $filter = $config->getFilter();
        $relation = Relations::getRelation($filter['dataContainer'], $element->field);

        if (false === $relation) {
            return $this;
        }

        $operator = $this->getOperator($element, $defaultOperator, $dca, false);

        if (!$operator) {
            return $this;
        }

        /** @var FilterQueryBuilderComposeEvent $event */
        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new FilterQueryBuilderComposeEvent($this, $name, $operator, $value, $element, $config),
            FilterQueryBuilderComposeEvent::class
        );

        if (true === $event->getContinue() && !empty($event->getValue())) {
            $alias = $relation['table'].'_'.$name;

            $this->join($relation['reference_table'], $relation['table'], $alias,
                $alias.'.'.$relation['reference_field'].'='.$relation['reference_table'].'.'.$relation['reference']);

            $this->andWhere(
                $this->container->get('huh.utils.database')->composeWhereForQueryBuilder(
                    $this, $alias.'.'.$relation['related_field'], $event->getOperator(), $dca, $event->getValue()
                )
            );

            $this->groupBy($filter['dataContainer'].'.id');
        }

        return $this;
    }

    /**
     * Add category widget where clause.
     *
     * @param string $name            The field name
     * @param string $defaultOperator
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereCategoryWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null)
    {
        $filter = $config->getFilter();
        $data = $config->getData();
        $addJoin = true;

        if ($element->isInitial && $element->alternativeValueSource) {
            $value = $this->getValueFromAlternativeSource($data[$name], $data, $element, $name, $config, $dca);
        } else {
            $value = $data[$name] ?? AbstractType::getInitialValue($element, $this->contextualValues);
            $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);
        }

        // skip if empty to avoid sql error
        if (empty($value)) {
            return $this;
        }

        $alias = 'tl_category_association_'.$element->field;

        // check if table is already joined with the same alias
        if (\is_array($this->getQueryParts()['join'][$filter['dataContainer']])) {
            foreach ($this->getQueryParts()['join'][$filter['dataContainer']] as $join) {
                if ($join['joinAlias'] === $alias) {
                    $addJoin = false;
                }
            }
        }

        // join only if joinAlias do not already exist
        if ($addJoin) {
            $this->join(
                $filter['dataContainer'],
                'tl_category_association',
                $alias,
                "$alias.categoryField='$element->field' AND $alias.parentTable='".$filter['dataContainer']."' AND $alias.entity=".$filter['dataContainer'].'.id
            ');
        }

        $operator = $this->getOperator($element, $defaultOperator, $dca, false);

        if (!$operator) {
            return $this;
        }

        $this->andWhere(
            $this->container->get('huh.utils.database')->composeWhereForQueryBuilder(
                $this, $alias.'.category', $operator, $dca, $value
            )
        );

        // don't produce double results
        $this->addGroupBy($filter['dataContainer'].'.id');

        return $this;
    }

    protected function getValueFromAlternativeSource(
        $value,
        array $data,
        FilterConfigElementModel $element,
        string $name,
        FilterConfig $config,
        array $dca
    ) {
        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new AdjustFilterValueEvent($value ?? null, \is_array($data) ? $data : [], $element, $name, $config, $dca),
            AdjustFilterValueEvent::NAME
        );

        return $event->getValue();
    }

    protected function getGroupChoiceValues(FilterConfigElementModel $element, array $values): array
    {
        if (!$element->addGroupChoiceField) {
            return $values;
        }

        $options = [];

        foreach ($values as $value) {
            $options = array_merge($options, explode(',', $value));
        }

        return $options;
    }
}
