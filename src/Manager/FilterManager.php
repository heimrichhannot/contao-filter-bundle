<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Contao\Model\Collection;

class FilterManager
{
    protected ContaoFramework $framework;
    protected FilterSession $session;
    /**
     * All available filter configurations cache.
     *
     * @var FilterConfig[]
     */
    protected array $filters;
    protected Utils $utils;

    public function __construct(
        ContaoFramework $framework,
        FilterSession   $session,
        Utils           $utils
    ) {
        $this->framework = $framework;
        $this->session = $session;
        $this->utils = $utils;
    }

    /**
     * Get the query builder for a given filter id.
     *
     * @param array $skipElements Array with tl_filter_config_element ids that should be skipped on initQueryBuilder
     *
     * @return \HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder|null
     */
    public function getQueryBuilder(int $id, array $skipElements = [], bool $doNotChangeExistingQueryBuilder = false)
    {
        if (null === ($config = $this->findById($id))) {
            return null;
        }

        // always init query
        $queryBuilder = $config->initQueryBuilder($skipElements, FilterConfig::QUERY_BUILDER_MODE_DEFAULT, $doNotChangeExistingQueryBuilder);

        return $doNotChangeExistingQueryBuilder ? $queryBuilder : $config->getQueryBuilder();
    }

    /**
     * Get the query builder containing only initial filters for a given filter id.
     *
     * @param array $skipElements Array with tl_filter_config_element ids that should be skipped on initQueryBuilder
     *
     * @return \HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder|null
     */
    public function getInitialQueryBuilder(int $id, array $skipElements = [], bool $doNotChangeExistingQueryBuilder = false)
    {
        $config = $this->findById($id);
        if (null === $config) {
            return null;
        }

        // always init query
        $queryBuilder = $config->initQueryBuilder($skipElements,
            FilterConfig::QUERY_BUILDER_MODE_INITIAL_ONLY,
            $doNotChangeExistingQueryBuilder
        );

        return $doNotChangeExistingQueryBuilder ? $queryBuilder : $config->getQueryBuilder();
    }

    /**
     * Get the session key for a given filter config.
     *
     * @return string The unique session key
     */
    public function getSessionKey(array $filter): string
    {
        return 'huh.filter.session.'.$filter['name'] ?: $filter['id'];
    }

    /**
     * Find filter by id.
     *
     * @param bool $cache Disable for a fresh filter/query builder instance
     * @return FilterConfig|null The config or null if not found
     */
    public function findById(int $id, bool $cache = true): ?FilterConfig
    {
        if ($cache && isset($this->filters[$id])) {
            return $this->filters[$id];
        }

        /**
         * @var FilterConfigModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigModel::class);

        if (null === ($filter = $adapter->findByPk($id))) {
            return null;
        }

        $filterConfig = $this->getConfig($filter->row());

        if (!$cache) {
            return $filterConfig;
        }

        $this->filters[$id] = $filterConfig;

        return $this->filters[$id] ?? null;
    }

    /**
     * Get the config for a given filter.
     *
     * @param mixed|null $request The request to handle
     *
     * @return FilterConfig
     */
    protected function getConfig(array $filter, mixed $request = null): FilterConfig
    {
        /**
         * @var FilterConfig
         */
        $config = System::getContainer()->get('huh.filter.config');

        /**
         * @var FilterConfigElementModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigElementModel::class);

        // get the parent filter config
        if (isset($filter['type']) && FilterConfig::FILTER_TYPE_SORT === $filter['type']) {
            $parentFilter = $this->framework->getAdapter(FilterConfigModel::class)->findById($filter['parentFilter'])->row();

            if (!empty($parentFilter)) {
                $filter['filterFormAction'] = $parentFilter['filterFormAction'];
                $filter['dataContainer'] = $parentFilter['dataContainer'];
                $filter['name'] = $parentFilter['name'];
                $filter['method'] = $parentFilter['method'];
                $filter['mergeData'] = $parentFilter['mergeData'];
            }
        }

        /** @var Collection $elements */
        $elements = $adapter->findPublishedByPid($filter['id']);

        // merge multiple filters (e.g. initial filters and sort filter)
        if (null !== $elements
            && FilterConfig::FILTER_TYPE_DEFAULT === $filter['type']
            && $this->utils->container()->isFrontend())
        {
            $elementModels = $elements->getModels();
            $sort = $this->framework->getAdapter(FilterConfigModel::class)->findBy('parentFilter', $filter['id']);

            if (null !== $sort) {
                foreach ($sort as $s) {
                    $sortElements = $adapter->findPublishedByPid($s->id)->getModels();

                    if (\is_array($sortElements) && !empty($sortElements)) {
                        $elementModels = array_merge($elementModels, $sortElements);
                    }
                }
                $elements = new Collection($elementModels, FilterConfigElementModel::getTable());
            }
        }

        $sessionKey = $this->getSessionKey($filter);

        $config->init($sessionKey, $filter, $elements);

        return $config;
    }
}
