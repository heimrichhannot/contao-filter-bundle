<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Registry;


use HeimrichHannot\FilterBundle\Filter\FilterInterface;

class FilterRegistry
{
    /**
     * Filters.
     *
     * @var array
     */
    private $filters = [];

    /**
     * Filter groups
     *
     * @var array
     */
    private $groups = [];

    /**
     * Add the filter
     *
     * @param FilterInterface $filter
     * @param string $alias
     */
    public function add(FilterInterface $filter, string $alias, string $group): void
    {
        $this->filters[$alias]        = $filter;
        $this->groups[$group][$alias] = $filter;
    }

    /**
     * Get the filter.
     *
     * @param string $alias
     *
     * @throws \InvalidArgumentException
     *
     * @return FilterInterface
     */
    public function get(string $alias): FilterInterface
    {
        if (!array_key_exists($alias, $this->filters)) {
            throw new \InvalidArgumentException(sprintf('The filter "%s" does not exist', $alias));
        }

        return $this->filters[$alias];
    }

    /**
     * Get the filters.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return array_keys($this->filters);
    }

    public function getGroupedAliases(): array
    {
        $groups = [];

        foreach ($this->groups as $group => $filters)
        {
            $groups[$group] = array_keys($filters);
        }

        return $groups;
    }
}