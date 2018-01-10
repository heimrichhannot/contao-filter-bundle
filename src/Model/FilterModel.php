<?php

namespace HeimrichHannot\FilterBundle\Model;

/**
 * Reads and writes filter
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string $title
 * @property string $dataContainer
 *
 * @method FilterModel|null findById($id, array $opt = [])
 * @method FilterModel|null findByPk($id, array $opt = [])
 * @method FilterModel|null findOneBy($col, $val, array $opt = [])
 * @method FilterModel|null findOneByTstamp($val, array $opt = [])
 * @method FilterModel|null findOneByTitle($val, array $opt = [])
 * @method FilterModel|null findOneByDataContainer($val, array $opt = [])
 *
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByPid($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByTstamp($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByTitle($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findByDataContainer($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findMultipleByIds($val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findBy($col, $val, array $opt = [])
 * @method \Contao\Model\Collection|FilterModel[]|FilterModel|null findAll(array $opt = [])
 *
 * @method integer countById($id, array $opt = [])
 * @method integer countByTstamp($val, array $opt = [])
 * @method integer countByTitle($val, array $opt = [])
 * @method integer countByDataContainer($val, array $opt = [])
 *
 */
class FilterModel extends \Model
{
    protected static $strTable = 'tl_filter';
}