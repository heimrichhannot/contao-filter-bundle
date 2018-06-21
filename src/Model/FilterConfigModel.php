<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Model;

use Contao\Model;
use Contao\System;

/**
 * Reads and writes filter.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $dataContainer
 * @property string $method
 * @property string $action
 * @property string $template
 * @property string $name
 * @property string $cssClass
 * @property bool   $renderEmpty
 * @property bool   $published
 * @property string $start
 * @property string $stop
 *
 * @method FilterConfigModel|null                                              findById($id, array $opt = [])
 * @method FilterConfigModel|null                                              findByPk($id, array $opt = [])
 * @method FilterConfigModel|null                                              findOneBy($col, $val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByTstamp($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByDateAdded($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByTitle($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByDataContainer($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByMethod($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByAction($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByTemplate($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByName($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByCssClass($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByRenderEmpty($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByPublished($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByStart($val, array $opt = [])
 * @method FilterConfigModel|null                                              findOneByStop($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByPid($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByTstamp($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByDateAdded($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByTitle($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByMethod($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByAction($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByTemplate($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByName($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByCssClass($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByRenderEmpty($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByPublished($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByStart($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByStop($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findMultipleByIds($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findBy($col, $val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findAll(array $opt = [])
 * @method int                                                                 countById($id, array $opt = [])
 * @method int                                                                 countByTstamp($val, array $opt = [])
 * @method int                                                                 countByTitle($val, array $opt = [])
 * @method int                                                                 countByDataContainer($val, array $opt = [])
 * @method int                                                                 countByMethod($val, array $opt = [])
 * @method int                                                                 countByAction($val, array $opt = [])
 * @method int                                                                 countByTemplate($val, array $opt = [])
 * @method int                                                                 countByName($val, array $opt = [])
 * @method int                                                                 countByCssClass($val, array $opt = [])
 * @method int                                                                 countByRenderEmpty($val, array $opt = [])
 * @method int                                                                 countByPublished($val, array $opt = [])
 * @method int                                                                 countByStart($val, array $opt = [])
 * @method int                                                                 countByStop($val, array $opt = [])
 */
class FilterConfigModel extends Model
{
    protected static $strTable = 'tl_filter_config';

    /**
     * Find filters by multiple dataContainers.
     *
     * @param array $dataContainers
     * @param array $options
     *
     * @return \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null A collection of models or null if there are no filters
     */
    public function findByDataContainers(array $dataContainers, array $options = [])
    {
        $t = static::$strTable;
        $arrColumns = ["$t.dataContainer IN(".implode(',', array_map(function ($dataContainer) { return "'".addslashes($dataContainer)."'"; }, $dataContainers)).')'];

        /** @var Model $adapter */
        $adapter = System::getContainer()->get('contao.framework')->getAdapter(static::class);

        if (null === $adapter) {
            return null;
        }

        if (!isset($options['order'])) {
            $options['order'] = "$t.title DESC";
        }

        return $adapter->findBy($arrColumns, null, $options);
    }

    /**
     * Find published filters.
     *
     * @param array $options
     *
     * @return \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null A collection of models or null if there are no filters
     */
    public function findAllPublished(array $options = [])
    {
        $t = static::$strTable;
        $arrColumns = [];

        if (isset($arrOptions['ignoreFePreview']) || !defined('BE_USER_LOGGED_IN') || !BE_USER_LOGGED_IN) {
            $time = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        /** @var Model $adapter */
        $adapter = System::getContainer()->get('contao.framework')->getAdapter(static::class);

        if (null === $adapter) {
            return null;
        }

        return $adapter->findBy($arrColumns, null, $options);
    }
}
