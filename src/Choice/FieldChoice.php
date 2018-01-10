<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;
use HeimrichHannot\FilterBundle\Model\FilterModel;

class FieldChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        /**
         * @var FilterModel $adapter
         */
        $adapter = $this->framework->getAdapter(FilterModel::class);

        $choices = [];

        if (!$this->context->activeRecord->pid || ($filter = $adapter->findByPk($this->context->activeRecord->pid)) === null || $filter->dataContainer == '') {
            return $choices;
        }

        \Controller::loadDataContainer($filter->dataContainer);

        if (!isset($GLOBALS['TL_DCA'][$filter->dataContainer]['fields'])) {
            return $choices;
        }

        foreach ($GLOBALS['TL_DCA'][$filter->dataContainer]['fields'] as $name => $data) {
            $choices[$name] = ($data['label'][0] ?: $name) . ($data['label'][0] ? ' [' . $name .']' : '');
        }

        return $choices;
    }
}