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
use Doctrine\DBAL\Types\Types;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class FilterQueryPartProcessor
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function composeQueryPart(FilterTypeContext $context): FilterQueryPart
    {
        $queryPart = new FilterQueryPart($context);
        $queryPart->query = $this->composeQuery($context, $queryPart);

        return $queryPart;
    }

    private function composeQuery(FilterTypeContext $filterTypeContext, FilterQueryPart $filterQueryPart): string
    {
        $options = [
            'wildcardSuffix' => $filterTypeContext->getId(),
            'valueType' => null,
        ];

        if ($filterTypeContext->getValue() instanceof \DateTime) {
            /**
             * @var \DateTime
             */
            $date = $filterTypeContext->getValue();

            $filterTypeContext->setValue($date->getTimeStamp());
            $options['valueType'] = Types::INTEGER;
        }

        return $this->composeWhereForQueryBuilder(
            $filterTypeContext->getField(),
            $filterTypeContext->getOperator(),
            $filterTypeContext->getValue(),
            $GLOBALS['TL_DCA'][$filterTypeContext->getParent()->row()['dataContainer']]['fields'][$filterTypeContext->getField()],
            $filterQueryPart,
            $options
        );
    }

    /**
     * @param null  $value
     * @param array $options {
     *                          wildcardSuffix: string,
     *                          valueType: string
     *                       }
     */
    public function composeWhereForQueryBuilder(string $field, string $operator, $value, array $dca = null, FilterQueryPart $filterQueryPart, array $options = []): string
    {
        $queryBuilder = new QueryBuilder($this->connection);

        $valueType = $options['valueType'] ?? null;
        $wildcardSuffix = $options['wildcardSuffix'] ?? '';
        $wildcard = ':'.str_replace('.', '_', $field).$wildcardSuffix;
        $where = '';

        if (\is_string($value)) {
            $value = Controller::replaceInsertTags(\is_array($value) ? implode(' ', $value) : $value, false);
        }

        switch ($operator) {
            case DatabaseUtil::OPERATOR_LIKE:
                $where = $queryBuilder->expr()->like($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, '%'.$value.'%', $valueType);

                break;

            case DatabaseUtil::OPERATOR_UNLIKE:
                $where = $queryBuilder->expr()->notLike($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, '%'.$value.'%', $valueType);

                break;

            case DatabaseUtil::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);

                break;

            case DatabaseUtil::OPERATOR_UNEQUAL:
                $where = $queryBuilder->expr()->neq($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);

                break;

            case DatabaseUtil::OPERATOR_LOWER:
                $where = $queryBuilder->expr()->lt($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);

                break;

            case DatabaseUtil::OPERATOR_LOWER_EQUAL:
                $where = $queryBuilder->expr()->lte($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);

                break;

            case DatabaseUtil::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);

                break;

            case DatabaseUtil::OPERATOR_GREATER_EQUAL:
                $where = $queryBuilder->expr()->gte($field, $wildcard);
                $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);

                break;

            case DatabaseUtil::OPERATOR_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // if empty add an unfulfillable condition in order to avoid an sql error
                if (empty($value)) {
                    $where = $queryBuilder->expr()->eq(1, 2);
                } else {
                    $where = $queryBuilder->expr()->in($field, $wildcard);
                    $preparedValue = array_map(
                        function ($val) {
                            return addslashes(Controller::replaceInsertTags(trim($val), false));
                        },
                        $value
                    );
                    $this->applyParameterValues($filterQueryPart, $wildcard, $preparedValue, Connection::PARAM_STR_ARRAY);
                }

                break;

            case DatabaseUtil::OPERATOR_NOT_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // if empty add an unfulfillable condition in order to avoid an sql error
                if (empty($value)) {
                    $where = $queryBuilder->expr()->eq(1, 2);
                } else {
                    $where = $queryBuilder->expr()->notIn($field, $wildcard);
                    $preparedValue = array_map(
                        function ($val) {
                            return addslashes(Controller::replaceInsertTags(trim($val), false));
                        },
                        $value
                    );
                    $this->applyParameterValues($filterQueryPart, $wildcard, $preparedValue, Connection::PARAM_STR_ARRAY);
                }

                break;

            case DatabaseUtil::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($field);

                break;

            case DatabaseUtil::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($field);

                break;

            case DatabaseUtil::OPERATOR_IS_EMPTY:
                $where = $queryBuilder->expr()->eq($field, '\'\'');

                break;

            case DatabaseUtil::OPERATOR_IS_NOT_EMPTY:
                $where = $queryBuilder->expr()->neq($field, '\'\'');

                break;

            case DatabaseUtil::OPERATOR_REGEXP:
            case DatabaseUtil::OPERATOR_NOT_REGEXP:
                $where = $field.(DatabaseUtil::OPERATOR_NOT_REGEXP == $operator ? ' NOT REGEXP ' : ' REGEXP ').$wildcard;

                if (\is_array($dca) && isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                    // match a serialized blob
                    if (\is_array($value)) {
                        // build a regexp alternative, e.g. (:"1";|:"2";)
                        $this->applyParameterValues(
                            $filterQueryPart,
                            $wildcard,
                            '('.implode(
                                '|',
                                array_map(
                                    function ($val) {
                                        return ':"'.Controller::replaceInsertTags($val, false).'";';
                                    },
                                    $value
                                )
                            ).')',
                            $valueType
                        );
                    } else {
                        $this->applyParameterValues($filterQueryPart, $wildcard, ':"'.$value.'";', $valueType);
                    }
                } else {
                    // TODO: this makes no sense, yet
                    $this->applyParameterValues($filterQueryPart, $wildcard, $value, $valueType);
                }

                break;
        }

        return $where;
    }

    public function applyParameterValues(FilterQueryPart $filterQueryPart, string $wildcard, $value, $valueType): void
    {
        $filterQueryPart->setWildcard($wildcard);
        $filterQueryPart->setValue($value);
        $filterQueryPart->setValueType($valueType);
    }
}
