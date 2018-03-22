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
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
     * All available filter configurations cache
     *
     * @var FilterConfig[]
     */
    protected $filters;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param FilterSession $session
     */
    public function __construct(ContaoFrameworkInterface $framework, FilterSession $session)
    {
        $this->framework = $framework;
        $this->session   = $session;
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
        return 'huh.filter.session.' . $filter['name'] ?: $filter['id'];
    }

    /**
     * Find filter by id.
     *
     * @param int $id
     *
     * @return FilterConfig|null The config or null if not found
     */
    public function findById(int $id)
    {
        if (isset($this->filters[$id])) {
            return $this->filters[$id];
        }

        /**
         * @var FilterConfigModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigModel::class);

        if (null === ($filter = $adapter->findByPk($id))) {
            return null;
        }

        $this->filters[$id] = $this->getConfig($filter->row());

        return isset($this->filters[$id]) ? $this->filters[$id] : null;
    }

    /**
     * Get the config for a given filter
     *
     * @param array $filter
     * @param mixed $request The request to handle
     *
     * @return FilterConfig
     */
    protected function getConfig(array $filter, $request = null)
    {
        $sessionKey = $this->getSessionKey($filter);

        /**
         * @var FilterConfig
         */
        $config = System::getContainer()->get('huh.filter.config');

        /**
         * @var FilterConfigElementModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigElementModel::class);

        $elements = $adapter->findPublishedByPid($filter['id']);

        $config->init($sessionKey, $filter, $elements);

        return $config;
    }
}
