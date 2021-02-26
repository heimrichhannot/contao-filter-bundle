<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeCollection;
use HeimrichHannot\FilterBundle\FilterType\InitialFilterTypeInterface;

class LoadDataContainerListener
{
    /*
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
            $this->prepareFilterConfigElementDca();
        }
    }

    private function prepareFilterConfigElementDca()
    {
        $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];
        $types = $this->filterTypeCollection->getTypes();

        foreach ($types as $key => $type) {
            $prependPalette = '{general_legend},title,type;';

            if ($type instanceof InitialFilterTypeInterface) {
                $prependPalette = '{initial_legend},isInitial;'.$prependPalette;
            }

            $appendPalette = '{publish_legend},published;';

            $dca['palettes'][$key] = $type->getPalette($prependPalette, $appendPalette);
        }
    }
}
