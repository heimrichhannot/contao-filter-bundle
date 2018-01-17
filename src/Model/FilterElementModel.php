<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Model;

/**
 * Reads and writes filter.
 *
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property string $type
 * @property bool   $published
 * @property string $start
 * @property string $stop
 *
 * @method FilterElementModel|null                                               findById($id, array $opt = [])
 * @method FilterElementModel|null                                               findByPk($id, array $opt = [])
 * @method FilterElementModel|null                                               findOneBy($col, $val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByTstamp($val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByTitle($val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByType($val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByDataContainer($val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByPublished($val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByStart($val, array $opt = [])
 * @method FilterElementModel|null                                               findOneByStop($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByPid($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByTstamp($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByTitle($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByType($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByPublished($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByStart($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findByStop($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findMultipleByIds($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findBy($col, $val, array $opt = [])
 * @method \Contao\Model\Collection|FilterElementModel[]|FilterElementModel|null findAll(array $opt = [])
 * @method int                                                                   countById($id, array $opt = [])
 * @method int                                                                   countByTstamp($val, array $opt = [])
 * @method int                                                                   countByType($val, array $opt = [])
 * @method int                                                                   countByTitle($val, array $opt = [])
 * @method int                                                                   countByDataContainer($val, array $opt = [])
 * @method int                                                                   countByPublished($val, array $opt = [])
 * @method int                                                                   countByStart($val, array $opt = [])
 * @method int                                                                   countByStop($val, array $opt = [])
 */
class FilterElementModel extends \Model
{
    protected static $strTable = 'tl_filter_element';

    /**
     * Find published filte elements items by their parent ID.
     *
     * @param int   $intId      The filter ID
     * @param int   $intLimit   An optional limit
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|FilterElementModel[]|FilterElementModel|null A collection of models or null if there are no filter elements
     */
    public function findPublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        $t = static::$strTable;
        $arrColumns = ["$t.pid=?"];

        if (isset($arrOptions['ignoreFePreview']) || !defined('BE_USER_LOGGED_IN') || !BE_USER_LOGGED_IN) {
            $time = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.sorting ASC";
        }

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findBy($arrColumns, $intId, $arrOptions);
    }
}
