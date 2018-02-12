<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;

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
    const DEFAULT_TYPE_MAPPING = [
        'text' => DatabaseUtil::OPERATOR_LIKE,
        'text_concat' => DatabaseUtil::OPERATOR_LIKE,
        'textarea' => DatabaseUtil::OPERATOR_LIKE,
        'email' => DatabaseUtil::OPERATOR_LIKE,
        'integer' => DatabaseUtil::OPERATOR_EQUAL,
        'money' => DatabaseUtil::OPERATOR_EQUAL,
        'number' => DatabaseUtil::OPERATOR_EQUAL,
        'password' => DatabaseUtil::OPERATOR_EQUAL,
        'percent' => DatabaseUtil::OPERATOR_EQUAL,
        'search' => DatabaseUtil::OPERATOR_LIKE,
        'url' => DatabaseUtil::OPERATOR_LIKE,
        'range' => 'Spanne (range)',
        'tel' => DatabaseUtil::OPERATOR_LIKE,
        'color' => DatabaseUtil::OPERATOR_EQUAL,
        'choice' => DatabaseUtil::OPERATOR_EQUAL,
        'country' => DatabaseUtil::OPERATOR_EQUAL,
        'language' => DatabaseUtil::OPERATOR_EQUAL,
        'locale' => DatabaseUtil::OPERATOR_EQUAL,
        'hidden' => DatabaseUtil::OPERATOR_EQUAL,
        'checkbox' => DatabaseUtil::OPERATOR_EQUAL,
        'radio' => DatabaseUtil::OPERATOR_EQUAL,
        'initial' => DatabaseUtil::OPERATOR_LIKE,
        'date_time' => 'Datum & Zeit',
        'date' => 'Datum',
        'time' => 'Zeit',
        'date_range' => 'Datumsspanne (date range)',
    ];

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

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
    public function whereElement(FilterConfigElementModel $element, string $name, FilterConfig $config)
    {
        $filter = $config->getFilter();

        \Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            return $this;
        }

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        switch ($dca['inputType']) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $this->whereTagWidget($element, $name, $config, $dca);
                break;
            default:
                $this->whereWidget($element, $name, $config, $dca);
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
    public function whereWidget(FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca)
    {
        $data = $config->getData();

        if ($element->isInitial) {
            $value = $data[$name] ?? AbstractType::getInitialValue($element);

            if (null === $value || !$element->field) {
                return $this;
            }

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

            $operator = static::DEFAULT_TYPE_MAPPING[$element->type] ?? '';

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

        $this->andWhere(System::getContainer()->get('huh.utils.database')->composeWhereForQueryBuilder(
            $this, $element->field, $operator, $dca, $value
        ));

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
}
