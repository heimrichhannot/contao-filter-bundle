<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Model\FilterModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ParentChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        /**
         * @var FilterModel $adapter
         */
        $adapter = $this->framework->getAdapter(FilterModel::class);

        if (!$this->context->activeRecord->pid || ($filter = $adapter->findByPk($this->context->activeRecord->pid)) === null || $filter->dataContainer == '') {
            return $choices;
        }

        \Controller::loadDataContainer($filter->dataContainer);

        if (!isset($GLOBALS['TL_DCA'][$filter->dataContainer]['fields']['pid']['foreignKey'])) {
            return $choices;
        }

        $relation   = explode('.', $GLOBALS['TL_DCA'][$filter->dataContainer]['fields']['pid']['foreignKey'], 2);
        $objOptions = \Database::getInstance()->query("SELECT id, " . $relation[1] . " AS value FROM " . $relation[0] . " WHERE tstamp>0 ORDER BY value");

        $choices = [];

        while ($objOptions->next()) {
            $choices[$objOptions->id] = $objOptions->value;
        }

        return $choices;
    }
}