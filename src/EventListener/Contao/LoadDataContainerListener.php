<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener\Contao;

use Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

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

    /**
     * @throws Exception
     */
    public function __invoke(string $table): void
    {
        if ('tl_filter_config' === $table) {
            if ($this->connection->getSchemaManager()->listTableDetails('tl_filter_config')->hasColumn('action')) {
                $this->connection->executeQuery("ALTER TABLE tl_filter_config CHANGE action filterFormAction VARCHAR(255) DEFAULT '' NOT NULL");
            }
        }

        if ($table == FilterConfigElementModel::getTable()) {
            $this->newsCategoriesSupport();
        }
    }

    private function newsCategoriesSupport(): void
    {
        if (!class_exists(CodefogNewsCategoriesBundle::class)) {
            return;
        }

        $GLOBALS['TL_DCA'][FilterConfigElementModel::getTable()]['fields']['cf_newsCategories'] = [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'newsCategoriesPicker',
            'foreignKey' => 'tl_news_category.title',
            'options_callback' => ['codefog_news_categories.listener.data_container.news', 'onCategoriesOptionsCallback'],
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox'],
            'relation' => [
                'type' => 'haste-ManyToMany',
                'load' => 'lazy',
                'table' => 'tl_news_category',
                'referenceColumn' => 'news_id',
                'fieldColumn' => 'category_id',
                'relationTable' => 'tl_news_categories',
            ],
            'sql' => 'blob NULL',
        ];

        $GLOBALS['TL_DCA'][FilterConfigElementModel::getTable()]['fields']['cf_newsCategoriesChilds'] = [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ];
    }
}
