<?php

namespace HeimrichHannot\FilterBundle\Util;

use Contao\Controller;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

class DatabaseUtilPolyfill
{
    const SQL_CONDITION_OR = 'OR';
    const SQL_CONDITION_AND = 'AND';

    const OPERATOR_LIKE = 'like';
    const OPERATOR_UNLIKE = 'unlike';
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_UNEQUAL = 'unequal';
    const OPERATOR_LOWER = 'lower';
    const OPERATOR_LOWER_EQUAL = 'lowerequal';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_GREATER_EQUAL = 'greaterequal';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'notin';
    const OPERATOR_IS_NULL = 'isnull';
    const OPERATOR_IS_NOT_NULL = 'isnotnull';
    const OPERATOR_REGEXP = 'regexp';
    const OPERATOR_NOT_REGEXP = 'notregexp';
    const OPERATOR_IS_EMPTY = 'isempty';
    const OPERATOR_IS_NOT_EMPTY = 'isnotempty';

    const ON_DUPLICATE_KEY_IGNORE = 'IGNORE';
    const ON_DUPLICATE_KEY_UPDATE = 'UPDATE';

    const OPERATORS = [
        self::OPERATOR_LIKE,
        self::OPERATOR_UNLIKE,
        self::OPERATOR_EQUAL,
        self::OPERATOR_UNEQUAL,
        self::OPERATOR_LOWER,
        self::OPERATOR_LOWER_EQUAL,
        self::OPERATOR_GREATER,
        self::OPERATOR_GREATER_EQUAL,
        self::OPERATOR_IN,
        self::OPERATOR_NOT_IN,
        self::OPERATOR_IS_NULL,
        self::OPERATOR_IS_NOT_NULL,
        self::OPERATOR_IS_EMPTY,
        self::OPERATOR_IS_NOT_EMPTY,
        self::OPERATOR_REGEXP,
        self::OPERATOR_NOT_REGEXP,
    ];

    const NEGATIVE_OPERATORS = [
        self::OPERATOR_UNLIKE,
        self::OPERATOR_UNEQUAL,
        self::OPERATOR_NOT_IN,
        self::OPERATOR_IS_NULL,
        self::OPERATOR_IS_EMPTY,
        self::OPERATOR_NOT_REGEXP,
    ];

    /**
     * Maps operators of this class to its corresponding Doctrine ExpressionBuilder method.
     */
    const OPERATOR_MAPPING = [
        self::OPERATOR_LIKE => 'like',
        self::OPERATOR_UNLIKE => 'notLike',
        self::OPERATOR_EQUAL => 'eq',
        self::OPERATOR_UNEQUAL => 'neq',
        self::OPERATOR_LOWER => 'lt',
        self::OPERATOR_LOWER_EQUAL => 'lte',
        self::OPERATOR_GREATER => 'gt',
        self::OPERATOR_GREATER_EQUAL => 'gte',
        self::OPERATOR_IN => 'in',
        self::OPERATOR_NOT_IN => 'notIn',
        self::OPERATOR_IS_NULL => 'isNull',
        self::OPERATOR_IS_NOT_NULL => 'isNotNull',
    ];

    public function __construct(
        private InsertTagParser $insertTagParser
    ) {}

    /**
     * Computes a MySQL condition appropriate for the given operator.
     *
     * @return array Returns array($strQuery, $arrValues)
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Database/DatabaseUtil.php#L425
     * @noinspection PhpDuplicateSwitchCaseBodyInspection
     */
    public function computeCondition(string $field, string $operator, $value, string $table = null, bool $skipTablePrefix = false): array
    {
        $operator = trim(strtolower($operator));
        $values = [];
        $pattern = '?';
        $addQuotes = false;

        $explodedField = explode('.', $field);

        // remove table if already added to field name
        if (\count($explodedField) > 1) {
            $field = end($explodedField);
        }

        if ($table) {
            Controller::loadDataContainer($table);

            $dca = &$GLOBALS['TL_DCA'][$table]['fields'][$field];

            if (isset($dca['sql']) && false !== stripos($dca['sql'], 'blob')) {
                $addQuotes = true;
            }
        }

        switch ($operator) {
            case static::OPERATOR_UNLIKE:
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $values[] = $this->insertTagParser->replace('%'.($addQuotes ? '"'.$val.'"' : $val).'%');
                    }

                    break;
                }
                $values[] = $this->insertTagParser->replace('%'.($addQuotes ? '"'.$value.'"' : $value).'%');
                break;

            case static::OPERATOR_EQUAL:
                $values[] = $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value);
                break;

            case static::OPERATOR_UNEQUAL:
            case '<>':
                $values[] = $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value);
                break;

            case static::OPERATOR_LOWER:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value);

                break;

            case static::OPERATOR_GREATER:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value);

                break;

            case static::OPERATOR_LOWER_EQUAL:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value);

                break;

            case static::OPERATOR_GREATER_EQUAL:
                $pattern = 'CAST(? AS DECIMAL)';
                $values[] = $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value);

                break;

            case static::OPERATOR_IN:
                $value = array_filter(explode(',', $this->insertTagParser->replace($value)));

                // skip if empty to avoid sql error
                if (empty($value)) {
                    break;
                }

                $pattern = '('.implode(
                        ',',
                        array_map(
                            function ($val) {
                                return '"'.addslashes(trim($val)).'"';
                            },
                            $value
                        )
                    ).')';

                break;

            case static::OPERATOR_NOT_IN:
                $value = array_filter(explode(',', $this->insertTagParser->replace($value)));

                // skip if empty to avoid sql error
                if (empty($value)) {
                    break;
                }

                $pattern = '('.implode(
                        ',',
                        array_map(
                            function ($val) {
                                return '"'.addslashes(trim($val)).'"';
                            },
                            $value
                        )
                    ).')';

                break;

            case static::OPERATOR_IS_NULL:
            case static::OPERATOR_IS_NOT_NULL:
            case static::OPERATOR_IS_EMPTY:
            case static::OPERATOR_IS_NOT_EMPTY:
                $pattern = '';

                break;

            default:
                if (\is_array($value)) {
                    foreach ($value as $val) {
                        $values[] = $this->insertTagParser->replace('%'.($addQuotes ? '"'.$val.'"' : $val).'%');
                    }

                    break;
                }
                $values[] = $this->insertTagParser->replace('%'.($addQuotes ? '"'.$value.'"' : $value).'%');

                break;
        }

        $operator = $this->transformVerboseOperator($operator);

        return [(!$skipTablePrefix && $table ? $table.'.' : '')."$field $operator $pattern", $values];
    }

    /**
     * Transforms verbose operators to valid MySQL operators (aka junctors).
     * Supports: like, unlike, equal, unequal, lower, greater, lowerequal, greaterequal, in, notin.
     *
     * @return string|false The transformed operator or false if not supported
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Database/DatabaseUtil.php#L366
     */
    public function transformVerboseOperator(string $verboseOperator): string|false
    {
        return match ($verboseOperator) {
            static::OPERATOR_LIKE => 'LIKE',
            static::OPERATOR_UNLIKE => 'NOT LIKE',
            static::OPERATOR_EQUAL => '=',
            static::OPERATOR_UNEQUAL => '!=',
            static::OPERATOR_LOWER => '<',
            static::OPERATOR_GREATER => '>',
            static::OPERATOR_LOWER_EQUAL => '<=',
            static::OPERATOR_GREATER_EQUAL => '>=',
            static::OPERATOR_IN => 'IN',
            static::OPERATOR_NOT_IN => 'NOT IN',
            static::OPERATOR_IS_NULL => 'IS NULL',
            static::OPERATOR_IS_NOT_NULL => 'IS NOT NULL',
            static::OPERATOR_IS_EMPTY => '=""',
            static::OPERATOR_IS_NOT_EMPTY => '!=""',
            default => false,
        };
    }

    /**
     * @param array $options {wildcardSuffix: string}
     * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Database/DatabaseUtil.php#L569
     */
    public function composeWhereForQueryBuilder(QueryBuilder $queryBuilder, string $field, string $operator, array $dca = null, $value = null, array $options = []): string
    {
        $wildcardSuffix = $options['wildcardSuffix'] ?? '';
        $wildcard = ':'.str_replace('.', '_', $field).$wildcardSuffix;
        $wildcardParameterName = substr($wildcard, 1);
        $where = '';

        // remove dot for table prefixes
        if (str_contains($wildcard, '.')) {
            $wildcard = str_replace('.', '_', $wildcard);
        }

        switch ($operator) {
            case static::OPERATOR_LIKE:
                $where = $queryBuilder->expr()->like($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, '%'.$this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value).'%');

                break;

            case static::OPERATOR_UNLIKE:
                $where = $queryBuilder->expr()->notLike($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, '%'.$this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value).'%');

                break;

            case static::OPERATOR_EQUAL:
                $where = $queryBuilder->expr()->eq($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));

                break;

            case static::OPERATOR_UNEQUAL:
                $where = $queryBuilder->expr()->neq($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));

                break;

            case static::OPERATOR_LOWER:
                $where = $queryBuilder->expr()->lt($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));

                break;

            case static::OPERATOR_LOWER_EQUAL:
                $where = $queryBuilder->expr()->lte($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));

                break;

            case static::OPERATOR_GREATER:
                $where = $queryBuilder->expr()->gt($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));

                break;

            case static::OPERATOR_GREATER_EQUAL:
                $where = $queryBuilder->expr()->gte($field, $wildcard);
                $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));

                break;

            case static::OPERATOR_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // if empty add an unfulfillable condition in order to avoid an sql error
                if (empty($value)) {
                    $where = $queryBuilder->expr()->eq(1, 2);
                } else {
                    $where = $queryBuilder->expr()->in($field, $wildcard);
                    $preparedValue = array_map(
                        function ($val) {
                            return addslashes($this->insertTagParser->replace(trim($val)));
                        },
                        $value
                    );
                    $queryBuilder->setParameter($wildcardParameterName, $preparedValue, ArrayParameterType::STRING);
                }

                break;

            case static::OPERATOR_NOT_IN:
                $value = array_filter(!\is_array($value) ? explode(',', $value) : $value);

                // if empty add an unfulfillable condition in order to avoid an sql error
                if (empty($value)) {
                    $where = $queryBuilder->expr()->eq(1, 2);
                } else {
                    $where = $queryBuilder->expr()->notIn($field, $wildcard);
                    $preparedValue = array_map(
                        function ($val) {
                            return addslashes($this->insertTagParser->replace(trim($val)));
                        },
                        $value
                    );
                    $queryBuilder->setParameter($wildcardParameterName, $preparedValue, ArrayParameterType::STRING);
                }

                break;

            case static::OPERATOR_IS_NULL:
                $where = $queryBuilder->expr()->isNull($field);

                break;

            case static::OPERATOR_IS_NOT_NULL:
                $where = $queryBuilder->expr()->isNotNull($field);

                break;

            case static::OPERATOR_IS_EMPTY:
                $where = $queryBuilder->expr()->eq($field, '\'\'');

                break;

            case static::OPERATOR_IS_NOT_EMPTY:
                $where = $queryBuilder->expr()->neq($field, '\'\'');

                break;

            case static::OPERATOR_REGEXP:
            case static::OPERATOR_NOT_REGEXP:
                $where = $field.(self::OPERATOR_NOT_REGEXP == $operator ? ' NOT REGEXP ' : ' REGEXP ').$wildcard;

                if (\is_array($dca) && isset($dca['eval']['multiple']) && $dca['eval']['multiple']) {
                    // match a serialized blob
                    if (\is_array($value)) {
                        // build a regexp alternative, e.g. (:"1";|:"2";)
                        $queryBuilder->setParameter(
                            $wildcardParameterName,
                            '('.implode(
                                '|',
                                array_map(
                                    function ($val) {
                                        return ':"'.$this->insertTagParser->replace($val).'";';
                                    },
                                    $value
                                )
                            ).')'
                        );
                    } else {
                        $queryBuilder->setParameter($wildcardParameterName, ':"'.$this->insertTagParser->replace($value).'";');
                    }
                } else {
                    // TODO: this makes no sense, yet
                    $queryBuilder->setParameter($wildcardParameterName, $this->insertTagParser->replace(\is_array($value) ? implode(' ', $value) : $value));
                }

                break;
        }

        return $where;
    }
}