<?php

namespace HeimrichHannot\FilterBundle\Model;

class FilterElementModel extends \Model
{
    protected static $strTable = 'tl_filter_element';

    /**
     * Find published filte elements items by their parent ID
     *
     * @param integer $intId The filter ID
     * @param integer $intLimit An optional limit
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|FilterElementModel[]|FilterElementModel|null A collection of models or null if there are no filter elements
     */
    public static function findPublishedByPid($intId, $intLimit = 0, array $arrOptions = [])
    {
        $t          = static::$strTable;
        $arrColumns = ["$t.pid=?"];

        if (isset($arrOptions['ignoreFePreview']) || !BE_USER_LOGGED_IN) {
            $time         = \Date::floorToMinute();
            $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
        }

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = "$t.sorting DESC";
        }

        if ($intLimit > 0) {
            $arrOptions['limit'] = $intLimit;
        }

        return static::findBy($arrColumns, $intId, $arrOptions);
    }
}