<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;


interface FilterQueryBuilderInterface
{
    /**
     * Add filter columns
     *
     * @return array
     */
    public function addColumns(array $columns);

    /**
     * Get filter columns
     *
     * @return array
     */
    public function getColumns() : array;

    /**
     * Set filter columns
     *
     * @param array $columns
     */
    public function setColumns(array $columns);

    /**
     * Add filter values
     *
     * @param mixed $filterValues
     */
    public function addValues($values);


    /**
     * Get filter values
     * @return array|null
     */
    public function getValues();

    /**
     * Set filter values
     *
     * @param array|null $values
     */
    public function setValues($values);

    /**
     * Add filter options
     *
     * @param array $filterOptions
     */
    public function addOptions(array $options);

    /**
     * Get filter options
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Set filter options
     * @param array $options
     */
    public function setOptions(array $options);
}