<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;


class FilterQueryBuilder implements FilterQueryBuilderInterface
{
    /**
     * Filter statement columns
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Filter statement values
     *
     * @var null|array
     */
    protected $values = null;

    /**
     * Filter statement options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Add filter columns
     *
     * @return array
     */
    public function addColumns(array $columns)
    {
        $this->columns = array_merge($this->columns, $columns);
    }

    /**
     * Get filter columns
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set filter columns
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * Add filter values
     *
     * @param mixed $filterValues
     */
    public function addValues($values)
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        $this->values = array_merge(!is_array($this->values) ? [] : $this->values, $values);
    }


    /**
     * Get filter values
     * @return array|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set filter values
     *
     * @param array|null $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * Add filter options
     *
     * @param array $filterOptions
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Get filter options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set filter options
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}