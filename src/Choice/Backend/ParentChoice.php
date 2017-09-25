<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice\Backend;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;
use HeimrichHannot\FilterBundle\Model\FilterModel;

class ParentChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collectChoices()
    {
        $choices = [];

        if (!$this->context->activeRecord->pid || ($filter = FilterModel::findByPk($this->context->activeRecord->pid)) === null || $filter->dataContainer == '') {
            return $choices;
        }

        \Controller::loadDataContainer($filter->dataContainer);

        $relation = explode('.', $GLOBALS['TL_DCA']['tl_news']['fields']['pid']['foreignKey'], 2);
        $objOptions = \Database::getInstance()->query("SELECT id, " . $relation[1] . " AS value FROM " . $relation[0] . " WHERE tstamp>0 ORDER BY value");

        $choices = array();

        while ($objOptions->next())
        {
            $choices[$objOptions->id] = $objOptions->value;
        }

        return $choices;
    }
}