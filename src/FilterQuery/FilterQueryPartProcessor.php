<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterQuery;

use Contao\Controller;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\FilterBundle\Type\AbstractFilterType;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;

class FilterQueryPartProcessor
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var DateUtil
     */
    protected $dateUtil;

    /**
     * @var DatabaseUtil
     */
    protected $databaseUtil;

    public function __construct(Connection $connection, DateUtil $dateUtil, DatabaseUtil $databaseUtil)
    {
        $this->connection = $connection;
        $this->dateUtil = $dateUtil;
        $this->databaseUtil = $databaseUtil;
    }

    public function composeQueryPart(FilterTypeContext $filterTypeContext): FilterQueryPart
    {
        return new FilterQueryPart($filterTypeContext);
    }

    public function composeWhereForQueryBuilder(FilterQueryPart $filterQueryPart, QueryBuilder $queryBuilder, array $dca = []): string
    {
        $where = '';

        $value = $filterQueryPart->getValue();

        if (\is_string($value)) {
            $value = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);
        }

        $this->updateInitialFilterProperties($filterQueryPart);

        switch ($filterQueryPart->getOperator()) {
            case DatabaseUtil::OPERATOR_LIKE:
                $where = $queryBuilder->expr()->like($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                if (('%' === substr($value, -1)) || ('%' === substr($value, 0, 1))) {
                    $filterQueryPart->setValue($value);
                } else {
                    $filterQueryPart->setValue('%'.$value.'%');
                }

                break;

            case DatabaseUtil::OPERATOR_UNLIKE:
                $where = $queryBuilder->expr()->notLike($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                if (('%' === substr($value, -1)) || ('%' === substr($value, 0, 1))) {
                    $filterQueryPart->setValue($value);
                } else {
                    $filterQueryPart->setValue('%'.$value.'%');
                }

                break;

            case DatabaseUtil::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                break;

            case DatabaseUtil::OPERATOR_UNEQUAL:
                $where = $queryBuilder->expr()->neq($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                break;

            case DatabaseUtil::OPERATOR_LOWER:
                $where = $queryBuilder->expr()->lt($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                break;

            case DatabaseUtil::OPERATOR_LOWER_EQUAL:
                $where = $queryBuilder->expr()->lte($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                break;

            case DatabaseUtil::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                break;

            case DatabaseUtil::OPERATOR_GREATER_EQUAL:
                $where = $queryBuilder->expr()->gte($filterQueryPart->getField(), $filterQueryPart->getWildcard());

                break;

            case DatabaseUtil::OPERATOR_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // if empty add an unfulfillable condition in order to avoid an sql error
                if (empty($value)) {
                    $where = $queryBuilder->expr()->eq(1, 2);
                } else {
                    $where = $queryBuilder->expr()->in($filterQueryPart->getField(), $filterQueryPart->getWildcard());
                    $preparedValue = array_map(
                        function ($val) {
                            return addslashes(Controller::replaceInsertTags(trim($val), false));
                        },
                        $value
                    );

                    $filterQueryPart->setValue($preparedValue);
                    $filterQueryPart->setValueType(Connection::PARAM_STR_ARRAY);
                }

                break;

            case DatabaseUtil::OPERATOR_NOT_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // if empty add an unfulfillable condition in order to avoid an sql error
                if (empty($value)) {
                    $where = $queryBuilder->expr()->eq(1, 2);
                } else {
                    $where = $queryBuilder->expr()->notIn($filterQueryPart->getField(), $filterQueryPart->getWildcard());
                    $preparedValue = array_map(
                        function ($val) {
                            return addslashes(Controller::replaceInsertTags(trim($val), false));
                        },
                        $value
                    );

                    $filterQueryPart->setValue($preparedValue);
                    $filterQueryPart->setValueType(Connection::PARAM_STR_ARRAY);
                }

                break;

            case DatabaseUtil::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($filterQueryPart->getField());

                break;

            case DatabaseUtil::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($filterQueryPart->getField());

                break;

            case DatabaseUtil::OPERATOR_IS_EMPTY:
                $where = $queryBuilder->expr()->eq($filterQueryPart->getField(), '\'\'');

                break;

            case DatabaseUtil::OPERATOR_IS_NOT_EMPTY:
                $where = $queryBuilder->expr()->neq($filterQueryPart->getField(), '\'\'');

                break;

            case DatabaseUtil::OPERATOR_REGEXP:
            case DatabaseUtil::OPERATOR_NOT_REGEXP:
                $where = $filterQueryPart->getField().(DatabaseUtil::OPERATOR_NOT_REGEXP == $filterQueryPart->getOperator() ? ' NOT REGEXP ' : ' REGEXP ').$filterQueryPart->getWildcard();

                if (\is_array($dca) && isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                    // match a serialized blob
                    if (\is_array($value)) {
                        // build a regexp alternative, e.g. (:"1";|:"2";)

                        $preparedValue =
                            '('.implode(
                                '|',
                                array_map(
                                    function ($val) {
                                        return ':"'.Controller::replaceInsertTags($val, false).'";';
                                    },
                                    $value
                                )
                            ).')';

                        $filterQueryPart->setValue($preparedValue);
                    } else {
                        $filterQueryPart->setValue(':"'.$value.'";');
                    }
                }

                break;
        }

        return $where;
    }

    public function updateInitialFilterProperties(FilterQueryPart $filterPart): void
    {
        if (null === $filterPart->getInitialValue()) {
            return;
        }

        switch ($filterPart->getInitialValueType()) {
            case AbstractFilterType::VALUE_TYPE_SCALAR:
                $filterPart->setValue($filterPart->getInitialValue());

                break;

            case AbstractFilterType::VALUE_TYPE_ARRAY:
                $filterPart->setValue($filterPart->getInitialValue());
                $filterPart->setValueType(Connection::PARAM_STR_ARRAY);

                break;
        }
    }
}
