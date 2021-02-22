<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeCollection;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;

class LoadDataContainerListener
{
    /**
     * @var FilterTypeCollection
     */
    protected $filterTypeCollection;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, FilterTypeCollection $filterTypeCollection)
    {
        $this->connection = $connection;
        $this->filterTypeCollection = $filterTypeCollection;
    }

    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if ('tl_filter_config' === $table) {
            if ($this->connection->getSchemaManager()->listTableDetails('tl_filter_config')->hasColumn('action')) {
                $this->connection->executeQuery("ALTER TABLE tl_filter_config CHANGE action filterFormAction VARCHAR(255) DEFAULT '' NOT NULL");
            }
        }

        if ('tl_filter_config_element' === $table) {
            $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];
            $types = $this->filterTypeCollection->getTypes();

            $filterTypeContext = $this->getFilterTypeContext();

            foreach ($types as $key => $type) {
                $dca['palettes'][$key] = $filterTypeContext;
            }
        }
    }

    private function getFilterTypeContext(): FilterTypeContext
    {
        return new FilterTypeContext();
    }
}
