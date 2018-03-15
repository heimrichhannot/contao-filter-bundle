<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

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

    public function __construct(ContaoFrameworkInterface $framework, Connection $connection)
    {
        parent::__construct($connection);
        $this->framework = $framework;
    }

    /**
     * Add where clause based on an element.
     *
     * @param FilterConfigElementModel $element
     * @param string                   $name    The field name
     * @param FilterConfig             $config
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereElement(FilterConfigElementModel $element, string $name, FilterConfig $config, string $defaultOperator)
    {
        $filter = $config->getFilter();

        \Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            return $this;
        }

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        if ($dca['eval']['isCategoryField']) {
            $this->whereCategoryWidget($element, $name, $config, $dca);

            return $this;
        }

        switch ($dca['inputType']) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $this->whereTagWidget($element, $name, $config, $dca);
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
     * @param string                   $name    The field name
     * @param FilterConfig             $config
     * @param array                    $dca
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca, string $defaultOperator = null)
    {
        $data = $config->getData();

        if ($element->isInitial) {
            $value = $data[$name] ?? AbstractType::getInitialValue($element, $this->contextualValues);

            if (null === $value || !$element->field) {
                return $this;
            }

            // never replace non initial Inserttags (user inputs), avoid injection and never cache to avoid esi:tags
            $value = Controller::replaceInsertTags($value, false);

            $operator = $element->operator;

            // db value is a serialized blob
            if (isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                $operator = DatabaseUtil::OPERATOR_REGEXP;
            }
        } else {
            $value = $data[$name] ?? ($element->customValue ? $element->value : null);

            if (null === $value || !$element->field) {
                return $this;
            }

            $operator = $defaultOperator;

            // db value is a serialized blob
            if (isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                $operator = DatabaseUtil::OPERATOR_REGEXP;
            }

            if ($element->customOperator && $element->operator) {
                $operator = $element->operator;
            }

            if (!$operator) {
                return $this;
            }
        }

        $this->andWhere(System::getContainer()->get('huh.utils.database')->composeWhereForQueryBuilder($this, $element->field, $operator, $dca, $value));

        return $this;
    }

    public function addContextualValue($elementId, $value)
    {
        $this->contextualValues[$elementId] = $value;
    }

    /**
     * Add tag widget where clause.
     *
     * @param FilterConfigElementModel $element
     * @param string                   $name    The field name
     * @param FilterConfig             $config
     * @param array                    $dca
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereTagWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca)
    {
        $filter = $config->getFilter();
        $data = $config->getData();
        $value = $data[$name];
        $relation = Relations::getRelation($filter['dataContainer'], $element->field);

        if (false === $relation || null === $value) {
            return $this;
        }

        $alias = $relation['table'].'_'.$name;

        $this->join($relation['reference_table'], $relation['table'], $alias, $alias.'.'.$relation['reference_field'].'='.$relation['reference_table'].'.'.$relation['reference']);
        $this->andWhere($this->expr()->in($alias.'.'.$relation['related_field'], $value));

        return $this;
    }

    /**
     * Add category widget where clause.
     *
     * @param FilterConfigElementModel $element
     * @param string                   $name    The field name
     * @param FilterConfig             $config
     * @param array                    $dca
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereCategoryWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca)
    {
        $filter = $config->getFilter();
        $data = $config->getData();
        $value = $data[$name];

        if (null === $value) {
            return $this;
        }

        $alias = 'tl_category_association_'.$element->field;

        $this->join($filter['dataContainer'], 'tl_category_association', $alias, "
        $alias.categoryField='$element->field' AND 
        $alias.parentTable='".$filter['dataContainer']."' AND
        $alias.entity=".$filter['dataContainer'].'.id
        ');
        $this->andWhere($this->expr()->in($alias.'.category', ":$alias"));

        $this->setParameter(":$alias", $value, \PDO::PARAM_STR);

        return $this;
    }

    /**
     * @return array
     */
    public function getContextualValues(): array
    {
        return $this->contextualValues;
    }
}
