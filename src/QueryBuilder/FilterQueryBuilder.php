<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;

use Codefog\HasteBundle\DcaRelationsManager;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Haste\Model\Relations;  # keep for class_exists check
use HeimrichHannot\FilterBundle\Util\DatabaseUtilPolyfill;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\AdjustFilterValueEvent;
use HeimrichHannot\FilterBundle\Event\FilterQueryBuilderComposeEvent;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\FilterCollection;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class FilterQueryBuilder extends QueryBuilder
{
    protected ContaoFramework $framework;
    protected array $contextualValues = [];
    /**
     * List of elements that should be skipped.
     *
     * @var FilterConfigElementModel[]
     */
    protected array $skip = [];
    protected InsertTagParser $insertTagParser;
    protected FilterCollection $filterCollection;
    protected Utils $utils;
    protected DatabaseUtilPolyfill $dbUtil;

    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        InsertTagParser $insertTagParser,
        FilterCollection $filterCollection,
        Utils $utils,
        DatabaseUtilPolyfill $dbUtil
    ) {
        parent::__construct($connection);
        $this->framework = $framework;
        $this->insertTagParser = $insertTagParser;
        $this->filterCollection = $filterCollection;
        $this->utils = $utils;
        $this->dbUtil = $dbUtil;
    }

    /**
     * Add where clause based on an element.
     *
     * @param string $name The field name
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereElement(FilterConfigElementModel $element, string $name, FilterConfig $config, string $defaultOperator): static
    {
        $filter = $config->getFilter();

        Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            return $this;
        }

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        if ($dca['eval']['isCategoryField'] ?? false) {
            $this->whereCategoryWidget($element, $name, $config, $dca, $this->dbUtil::OPERATOR_IN);

            return $this;
        }

        switch ($dca['inputType'] ?? null) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $this->whereTagWidget($element, $name, $config, $dca, $this->dbUtil::OPERATOR_IN);

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

        if (ChoiceType::TYPE == $element->type && isset($data[$name]) && \is_array($data[$name])) {
            $data[$name] = $this->getGroupChoiceValues($element, $data[$name]);
        }

        if ($element->isInitial) {
            $value = AbstractType::getInitialValue($element, $this->contextualValues);

            if ($element->alternativeValueSource) {
                $value = $this->getValueFromAlternativeSource($value, $data, $element, $name, $config, $dca);
            }

            if (!in_array($element->operator, [$this->dbUtil::OPERATOR_IS_EMPTY, $this->dbUtil::OPERATOR_IS_NOT_EMPTY], true)
                and
                null === $value || !$element->field)
            {
                return $this;
            }

            // never replace non initial Insert tags (user inputs), avoid injection and never cache to avoid esi:tags
            if (\is_array($value)) {
                foreach ($value as &$val) {
                    $val = $this->insertTagParser->replace($val);
                }
            } else {
                $value = $this->insertTagParser->replace($value);
            }

            $operator = $this->getOperator($element, $defaultOperator, $dca) ?: $defaultOperator;
        } else {
            $value = $data[$name] ?? ($element->customValue ? $element->value : null);

            $operator = $this->getOperator($element, $defaultOperator, $dca);
        }

        if (!$operator) {
            return $this;
        }

        /** @var class-string<AbstractType> $typeClass */
        $typeClass = $this->filterCollection->getClassByType($element->type);

        if ($typeClass) {
            $value = $typeClass::normalizeValue($value);
        }

        /** @var FilterQueryBuilderComposeEvent $event */
        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new FilterQueryBuilderComposeEvent($this, $name, $operator, $value, $element, $config),
            FilterQueryBuilderComposeEvent::class
        );

        $eventValue = $event->getValue();

        if ($event->getContinue()
            and
            !empty($eventValue) || $eventValue === 0 || $eventValue === '0')
        {
            $this->andWhere(
                $this->dbUtil->composeWhereForQueryBuilder(
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

    public function addContextualValue($elementId, $value): void
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
    public function getSkip(): array
    {
        return $this->skip ??= [];
    }

    /**
     * Set filter skip elements.
     *
     * @param FilterConfigElementModel[] $skip
     */
    public function setSkip(array $skip): void
    {
        $this->skip = $skip;
    }

    /**
     * Add filter element to skip.
     */
    public function addSkip(FilterConfigElementModel $element): void
    {
        $this->skip[$element->id] = $element;
    }

    protected function getOperator(FilterConfigElementModel $element, string $operator, array $dca, bool $supportSerializedBlob = true): string
    {
        if ($dca['eval']['multiple'] ?? 'tagsinput' === ($dca['eval']['inputType'] ?? null)
            and
            $supportSerializedBlob)
        {
            if (\in_array($operator, $this->dbUtil::NEGATIVE_OPERATORS)) {
                // db value is a serialized blob
                if (str_contains($dca['sql'], 'blob')) {
                    $operator = $this->dbUtil::OPERATOR_NOT_REGEXP;
                } else {
                    $operator = $this->dbUtil::OPERATOR_NOT_IN;
                }
            } else {
                if (str_contains($dca['sql'], 'blob')) {
                    $operator = $this->dbUtil::OPERATOR_REGEXP;
                } else {
                    $operator = $this->dbUtil::OPERATOR_IN;
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
    protected function whereTagWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null): static
    {
        $data = $config->getData();

        if ($element->isInitial && $element->alternativeValueSource) {
            $value = $this->getValueFromAlternativeSource($data[$name] ?? null, $data, $element, $name, $config, $dca);
        } else {
            $value = $data[$name] ?? AbstractType::getInitialValue($element, $this->contextualValues);
            $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);
        }

        $filter = $config->getFilter();

        if (class_exists(DcaRelationsManager::class) && $this->container->has(DcaRelationsManager::class)) {
            $relation = $this->container->get(DcaRelationsManager::class)->getRelation($filter['dataContainer'], $element->field);
        } elseif (class_exists(Relations::class)) {
            $relation = Relations::getRelation($filter['dataContainer'], $element->field);
        } else {
            $relation = false;
        }

        if (false === $relation || null === $relation) {
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
                $this->dbUtil->composeWhereForQueryBuilder(
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
    protected function whereCategoryWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null): static
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
        $queryParts = $this->getQueryParts();

        if (isset($queryParts['join'][$filter['dataContainer']]) && \is_array($queryParts['join'][$filter['dataContainer']])) {
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
            $this->dbUtil->composeWhereForQueryBuilder(
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
            new AdjustFilterValueEvent($value ?? null, $data, $element, $name, $config, $dca),
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