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
 * @method FilterModel|null                                        findById($id, array $opt = [])
 * @method FilterModel|null                                        findByPk($id, array $opt = [])
 * @method FilterModel|null                                        findOneBy($col, $val, array $opt = [])
 * @method FilterModel|null                                        findOneByTstamp($val, array $opt = [])
 * @method FilterModel|null                                        findOneByTitle($val, array $opt = [])
 * @method FilterModel|null                                        findOneByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByPid($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByTstamp($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByTitle($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findMultipleByIds($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findBy($col, $val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findAll(array $opt = [])
 * @method int                                                     countById($id, array $opt = [])
 * @method int                                                     countByTstamp($val, array $opt = [])
 * @method int                                                     countByTitle($val, array $opt = [])
 * @method int                                                     countByDataContainer($val, array $opt = [])
 */
class FilterModel extends \Model
{
    protected static $strTable = 'tl_filter';
}
