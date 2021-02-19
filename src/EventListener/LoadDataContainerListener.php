<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class LoadDataContainerListener
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $locator;
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, ContainerInterface $locator)
    {
        $this->connection = $connection;
        $this->locator = $locator;
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
//            $textType = $this->locator->get('huh.filter.filter_type.type.text_type');
//            $dca['palettes'][$textType::TYPE] = $textType->getPalette();
        }
    }
}
