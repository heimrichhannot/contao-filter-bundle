<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Haste\Model\Relations;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\AdjustFilterValueEvent;
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
     * @param FilterConfigElementModel $element
     * @param string                   $name            The field name
     * @param FilterConfig             $config
     * @param string                   $defaultOperator
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

        if ($dca['eval']['isCategoryField'] && !$element->isInitial) {
            $this->whereCategoryWidget($element, $name, $config, $dca, DatabaseUtil::OPERATOR_IN);

            return $this;
        }

        switch ($dca['inputType']) {
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
     * @param FilterConfigElementModel $element
     * @param string                   $name            The field name
     * @param FilterConfig             $config
     * @param array                    $dca
     * @param string|null              $defaultOperator
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
            $value = $data[$name] ?? AbstractType::getInitialValue($element, $this->contextualValues);

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

            if (empty($value) || !$element->field) {
                return $this;
            }

            $operator = $this->getOperator($element, $defaultOperator, $dca);
        }

        if (!$operator) {
            return $this;
        }

        $this->andWhere(
            $this->container->get('huh.utils.database')->composeWhereForQueryBuilder(
                $this, $config->getFilter()['dataContainer'].'.'.$element->field, $operator, $dca, $value
            )
        );

        return $this;
    }

    public function addContextualValue($elementId, $value)
    {
        $this->contextualValues[$elementId] = $value;
    }

    /**
     * @return array
     */
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
     *
     * @param FilterConfigElementModel $element
     */
    public function addSkip(FilterConfigElementModel $element)
    {
        $this->skip[$element->id] = $element;
    }

    /**
     * @param FilterConfigElementModel $element
     * @param string                   $operator
     * @param array                    $dca
     * @param bool                     $supportSerializedBlob
     *
     * @return string
     */
    protected function getOperator(FilterConfigElementModel $element, string $operator, array $dca, bool $supportSerializedBlob = true): string
    {
        if (isset($dca['eval']['multiple']) && $dca['eval']['multiple'] && $supportSerializedBlob) {
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
     * @param FilterConfigElementModel $element
     * @param string                   $name            The field name
     * @param FilterConfig             $config
     * @param array                    $dca
     * @param string                   $defaultOperator
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereTagWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null)
    {
        $filter = $config->getFilter();
        $data = $config->getData();
        $value = $data[$name];
        $relation = Relations::getRelation($filter['dataContainer'], $element->field);

        if ($element->isInitial && $element->alternativeValueSource) {
            $value = $this->getValueFromAlternativeSource($value, $data, $element, $name, $config, $dca);
        }

        if (false === $relation || null === $value) {
            return $this;
        }

        $alias = $relation['table'].'_'.$name;

        $operator = $this->getOperator($element, $defaultOperator, $dca, false);

        if (!$operator) {
            return $this;
        }

        $this->join($relation['reference_table'], $relation['table'], $alias,
            $alias.'.'.$relation['reference_field'].'='.$relation['reference_table'].'.'.$relation['reference']);

        $this->andWhere(
            $this->container->get('huh.utils.database')->composeWhereForQueryBuilder(
                $this, $alias.'.'.$relation['related_field'], $operator, $dca, $value
            )
        );

        return $this;
    }

    /**
     * Add category widget where clause.
     *
     * @param FilterConfigElementModel $element
     * @param string                   $name            The field name
     * @param FilterConfig             $config
     * @param array                    $dca
     * @param string                   $defaultOperator
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereCategoryWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null)
    {
        $filter = $config->getFilter();
        $data = $config->getData();

        if ($element->isInitial && $element->alternativeValueSource) {
            $value = $this->getValueFromAlternativeSource($data[$name], $data, $element, $name, $config, $dca);
        } else {
            $value = $data[$name];
            $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);
        }

        // skip if empty to avoid sql error
        if (empty($value)) {
            return $this;
        }

        $alias = 'tl_category_association_'.$element->field;

        $this->join($filter['dataContainer'], 'tl_category_association', $alias, "
        $alias.categoryField='$element->field' AND 
        $alias.parentTable='".$filter['dataContainer']."' AND
        $alias.entity=".$filter['dataContainer'].'.id
        ');

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
        $event = $this->container->get('event_dispatcher')->dispatch(AdjustFilterValueEvent::NAME, new AdjustFilterValueEvent(
            $value ?? null, \is_array($data) ? $data : [], $element, $name, $config, $dca
        ));

        return $event->getValue();
    }

    /**
     * @param FilterConfigElementModel $element
     * @param array                    $values
     *
     * @return array
     */
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
