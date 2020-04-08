<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\FilterBundle\EventListener;


use Doctrine\DBAL\Connection;

class LoadDataContainerListener
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @Hook("loadDataContainer")
     */
    public function onLoadDataContainer(string $table): void
    {
        if ('tl_filter_config' === $table)
        {
            if ($this->connection->getSchemaManager()->listTableDetails('tl_filter_config')->hasColumn('action')) {
                $this->connection->executeQuery("ALTER TABLE tl_filter_config CHANGE action filterFormAction VARCHAR(255) DEFAULT '' NOT NULL");
            }
        }
    }
}