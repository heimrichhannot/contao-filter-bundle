<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Registry;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Entity\FilterSession;
use HeimrichHannot\FilterBundle\Model\FilterElementModel;
use HeimrichHannot\FilterBundle\Model\FilterModel;
use HeimrichHannot\Haste\Util\Url;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class FilterRegistry
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
     * Current file cache
     *
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * All available filter configurations
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
        $this->cache     = new FilesystemAdapter('huh.filter.registry', 0, \System::getContainer()->get('kernel')->getCacheDir());
    }

    /**
     * Clear the registry cache
     * @param array $ids An optional array if filter ids that should be cleared
     */
    public function clearCache(array $ids = [])
    {
        if (empty($ids)) {
            $this->cache->reset();
            return;
        }

        foreach ($ids as $id) {
            $cacheKey = $this->getCacheKeyById($id);

            if ($this->cache->hasItem($cacheKey)) {
                $this->cache->deleteItem($cacheKey);
            }
        }

        $this->cache->commit();
    }

    /**
     * Initialize the registry
     *
     * @param mixed $request The request to handle
     */
    public function init($request = null)
    {
        /**
         * @var FilterModel $adapter
         */
        $adapter = $this->framework->getAdapter(FilterModel::class);

        if (($filters = $adapter->findAll()) === null) {
            return;
        }

        while ($filters->next()) {
            $this->initFilter($filters->row(), $request);
        }
    }

    /**
     * Add an filter to the registry
     *
     * @param array $filter
     * @param mixed $request The request to handle
     *
     */
    protected function initFilter(array $filter, $request = null)
    {
        $cacheKey = $this->getCacheKeyById($filter['id']);

        $cache = $this->cache->getItem($cacheKey);

        /**
         * @var FilterConfig $config
         */
        $config = System::getContainer()->get('huh.filter.config');

        if (!$cache->isHit() || empty($cache->get()) || System::getContainer()->get('kernel')->isDebug()) {
            /**
             * @var FilterElementModel $adapter
             */
            $adapter = $this->framework->getAdapter(FilterElementModel::class);

            $elements = $adapter->findPublishedByPid($filter['id']);

            if (null !== $elements) {
                $elements = $elements->fetchAll();
            }

            $config->init($cacheKey, $filter, $elements);

            $cache->set($config);

            $this->cache->save($cache);
        }

        // always build the form and handle the request within the registry to have global access
        $this->handleForm($config, $request);

        $config = $cache->get();

        $this->filters[$cacheKey] = $config;
    }

    /**
     * @param FilterConfig $config
     * @param mixed $request The request to handle
     */
    protected function handleForm(FilterConfig $config, $request = null)
    {
        $cacheKey = $this->getCacheKeyById($config->getFilter()['id']);

        if (null === $config->getBuilder()) {
            $config->buildForm($this->session->getData($cacheKey));
        }

        if (null === $config->getBuilder()) {
            return;
        }

        $form = $config->getBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->session->setData($cacheKey, $data);

            // redirect to same page without filter parameters
            Controller::redirect(Url::removeQueryString([$form->getName()], $form->getConfig()->getAction() ?: null));
        }
    }

    /**
     * Get the cache key for a given filter id
     * @param int $id
     *
     * @return string The unique cache key
     */
    public function getCacheKeyById(int $id)
    {
        return 'huh.registry.filter.' . $id;
    }

    /**
     * Find filter by id
     * @param int $id
     *
     * @return FilterConfig|null The config or null if not found
     */
    public function findById(int $id)
    {
        $cacheKey = $this->getCacheKeyById($id);

        if ($this->cache->hasItem($cacheKey)) {
            return $this->cache->getItem($cacheKey)->get();
        }

        return null;
    }

    /**
     * @return FilterConfig[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}