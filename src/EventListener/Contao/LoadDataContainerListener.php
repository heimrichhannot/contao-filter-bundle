<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Doctrine\DBAL\Connection;

/**
 * @Hook("loadDataContainer")
 */
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

    public function __invoke(string $table): void
    {
        if ('tl_filter_config' === $table) {
            if ($this->connection->getSchemaManager()->listTableDetails('tl_filter_config')->hasColumn('action')) {
                $this->connection->executeQuery("ALTER TABLE tl_filter_config CHANGE action filterFormAction VARCHAR(255) DEFAULT '' NOT NULL");
            }
        }
    }
}
