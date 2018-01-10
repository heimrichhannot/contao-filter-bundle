<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterModel;

class FilterRegistry
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * All available filter configurations
     * @var FilterConfig[]
     */
    protected $filters;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Initialize the registry
     */
    public function init()
    {
        /**
         * @var FilterModel $adapter
         */
        $adapter = $this->framework->getAdapter(FilterModel::class);

        if (($filters = $adapter->findAll()) === null) {
            return;
        }

        while ($filters->next()) {
            /**
             * @var FilterConfig $config
             */
            $config = System::getContainer()->get('huh.filter.config');

            try {
                $config->init($filters->row());
            } catch (\Exception $e) {
                // if for instance some sql fields are not yet available, do not init filter
            }

            $this->filters[$filters->id] = $config;

            if (null === $config->getBuilder()) {
                // always build the form within the registry to have global access
                $config->buildForm();
            }
        }
    }

    /**
     * Find filter by id
     * @param int $id
     *
     * @return FilterConfig|null The config or null if not found
     */
    public function findById(int $id)
    {
        if (!isset($this->filters[$id])) {
            return null;
        }

        return $this->filters[$id];
    }

    /**
     * @return FilterConfig[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}