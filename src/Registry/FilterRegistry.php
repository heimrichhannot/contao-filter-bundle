<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Registry;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Doctrine\DBAL\DBALException;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Entity\FilterSession;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Model\FilterElementModel;
use HeimrichHannot\FilterBundle\Model\FilterModel;
use HeimrichHannot\Haste\Util\Url;
use PHPUnit\Util\Filter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Exception\TransformationFailedException;

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
     * Current file cache.
     *
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * All available filter configurations.
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
        $this->session   = $session;
        $this->cache     = new FilesystemAdapter('huh.filter.registry', 0, \System::getContainer()->get('kernel')->getCacheDir());
    }

    /**
     * Initialize the registry.
     *
     * @param mixed $request The request to handle
     */
    public function init($request = null)
    {
        /**
         * @var FilterModel
         */
        $adapter = $this->framework->getAdapter(FilterModel::class);

        if (null === ($filters = $adapter->findAll())) {
            return;
        }

        while ($filters->next()) {
            try {
                $this->initFilter($filters->row(), $request);
            } catch (DBALException $e) {
                // if fields does not exist in db, contao/install wont work anymore, catch error
            }
        }
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

        if (null === ($config->getQueryBuilder())) {
            $config->initQueryBuilder();
        }

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
     * @param int $id
     *
     * @return FilterConfig|null The config or null if not found
     */
    public function findById(int $id)
    {
        /**
         * @var FilterModel
         */
        $adapter = $this->framework->getAdapter(FilterModel::class);

        if (isset($this->filters[$id])) {
            return $this->filters[$id];
        }

        if (null === ($filter = $adapter->findByPk($id))) {
            return null;
        }

        $this->initFilter($filter->row());

        return $this->filters[$id];
    }

    /**
     * @return FilterConfig[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Add an filter to the registry.
     *
     * @param array $filter
     * @param mixed $request The request to handle
     */
    protected function initFilter(array $filter, $request = null)
    {
        $sessionKey = $this->getSessionKey($filter);

        /**
         * @var FilterConfig
         */
        $config = System::getContainer()->get('huh.filter.config');

        /**
         * @var FilterElementModel
         */
        $adapter = $this->framework->getAdapter(FilterElementModel::class);

        $elements = $adapter->findPublishedByPid($filter['id']);

        if (null !== $elements) {
            $elements = $elements->fetchAll();
        }

        $config->init($sessionKey, $filter, $elements);

        // always build the form and handle the request within the registry to have global access
        $this->handleForm($config, $request);

        $this->filters[$config->getId()] = $config;
    }

    /**
     * @param FilterConfig $config
     * @param mixed        $request The request to handle
     */
    protected function handleForm(FilterConfig $config, $request = null)
    {
        $sessionKey = $config->getSessionKey();

        if (null === $config->getBuilder()) {
            $config->buildForm($this->session->getData($sessionKey));
        }

        if (null === $config->getBuilder()) {
            return;
        }

        try {
            $form = $config->getBuilder()->getForm();
        } catch (TransformationFailedException $e) {
            // for instance field changed from single to multiple value, transform old session data will throw an TransformationFailedException -> clear session and build again with empty data
            $this->session->reset($sessionKey);
            $config->buildForm($this->session->getData($sessionKey));
            $form = $config->getBuilder()->getForm();
        }

        $form->handleRequest($request);

        if (null === $request) {
            $request = System::getContainer()->get('request_stack')->getCurrentRequest();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $url  = Url::removeQueryString([$form->getName()], $form->getConfig()->getAction() ?: null);

            // allow reset, support different form configuration with same form name
            if (null !== $form->getClickedButton() && in_array($form->getClickedButton()->getName(), $config->getResetNames(), true)) {
                $this->session->reset($sessionKey);
                $data = [];
                // redirect to same page without filter parameters
                $url = Url::removeQueryString([$form->getName()], $request->getUri() ?: null);
            }

            // do not save filter id in session
            unset($data[FilterType::FILTER_ID_NAME]);
            $this->session->setData($sessionKey, $data);

            // redirect to same page without filter parameters
            Controller::redirect($url);
        }
    }
}
