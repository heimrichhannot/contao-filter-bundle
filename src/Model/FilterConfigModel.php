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
 * @property string $dataContainer
 *
 * @method FilterConfigModel|null                                        findById($id, array $opt = [])
 * @method FilterConfigModel|null                                        findByPk($id, array $opt = [])
 * @method FilterConfigModel|null                                        findOneBy($col, $val, array $opt = [])
 * @method FilterConfigModel|null                                        findOneByTstamp($val, array $opt = [])
 * @method FilterConfigModel|null                                        findOneByTitle($val, array $opt = [])
 * @method FilterConfigModel|null                                        findOneByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByPid($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByTstamp($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByTitle($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findMultipleByIds($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findBy($col, $val, array $opt = [])
 * @method \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null findAll(array $opt = [])
 * @method int                                                     countById($id, array $opt = [])
 * @method int                                                     countByTstamp($val, array $opt = [])
 * @method int                                                     countByTitle($val, array $opt = [])
 * @method int                                                     countByDataContainer($val, array $opt = [])
 */
class FilterConfigModel extends \Model
{
    protected static $strTable = 'tl_filter_config';


    /**
     * Find published filters
     *
     * @param array $options
     *
     * @return \Contao\Model\Collection|FilterConfigModel[]|FilterConfigModel|null A collection of models or null if there are no filters
     */
    public function findAllPublished(array $options = [])
    {
        $t          = static::$strTable;
        $arrColumns = [];

        if (isset($arrOptions['ignoreFePreview']) || !defined('BE_USER_LOGGED_IN') || !BE_USER_LOGGED_IN) {
            $time         = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'".($time + 60)."') AND $t.published='1'";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.sorting ASC";
        }

        return static::findBy($arrColumns, null, $arrOptions);
    }
}
