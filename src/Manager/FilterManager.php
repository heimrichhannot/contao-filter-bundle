<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use Model\Collection;

class FilterManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var FilterSession
     */
    protected $session;

    /**
     * All available filter configurations cache.
     *
     * @var FilterConfig[]
     */
    protected $filters;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param FilterSession            $session
     */
    public function __construct(ContaoFrameworkInterface $framework, FilterSession $session)
    {
        $this->framework = $framework;
        $this->session = $session;
    }

    /**
     * Get the query builder for a given filter id.
     *
     * @param int $id
     *
     * @return \HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder|null
     */
    public function getQueryBuilder(int $id)
    {
        if (null === ($config = $this->findById($id))) {
            return null;
        }

        // always init query
        $config->initQueryBuilder();

        return $config->getQueryBuilder();
    }

    /**
     * Get the session key for a given filter config.
     *
     * @param array $filter
     *
     * @return string The unique session key
     */
    public function getSessionKey(array $filter)
    {
        return 'huh.filter.session.'.$filter['name'] ?: $filter['id'];
    }

    /**
     * Find filter by id.
     *
     * @param int  $id
     * @param bool $cache Disable for a fresh filter/querybuilder instance
     *
     * @return FilterConfig|null The config or null if not found
     */
    public function findById(int $id, $cache = true)
    {
        if (true === $cache && isset($this->filters[$id])) {
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

        if (false === $cache) {
            return $filterConfig;
        }

        $this->filters[$id] = $filterConfig;

        return isset($this->filters[$id]) ? $this->filters[$id] : null;
    }

    /**
     * Get the config for a given filter.
     *
     * @param array $filter
     * @param mixed $request The request to handle
     *
     * @return FilterConfig
     */
    protected function getConfig(array $filter, $request = null)
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
                $filter['action'] = $parentFilter['action'];
                $filter['dataContainer'] = $parentFilter['dataContainer'];
                $filter['name'] = $parentFilter['name'];
                $filter['method'] = $parentFilter['method'];
                $filter['mergeData'] = $parentFilter['mergeData'];
            }
        }

        /** @var Collection $elements */
        $elements = $adapter->findPublishedByPid($filter['id']);

        // merge multiple filters (e.g. inital filters and sort filter)
        if (null !== $elements && FilterConfig::FILTER_TYPE_DEFAULT === $filter['type'] && System::getContainer()->get('huh.utils.container')->isFrontend()) {
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
