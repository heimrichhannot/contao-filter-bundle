<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

/**
 * Class ParentChoice.
 */
class ParentChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        /**
         * @var FilterConfigModel
         */
        $adapter = $this->framework->getAdapter(FilterConfigModel::class);

        if (!$this->context->activeRecord->pid || null === ($filter = $adapter->findByPk($this->context->activeRecord->pid)) || '' === $filter->dataContainer) {
            return $choices;
        }

        Controller::loadDataContainer($filter->dataContainer);

        if (!isset($GLOBALS['TL_DCA'][$filter->dataContainer]['fields']['pid']['foreignKey'])) {
            return $choices;
        }

        $relation = explode('.', $GLOBALS['TL_DCA'][$filter->dataContainer]['fields']['pid']['foreignKey'], 2);
        $objOptions = \Database::getInstance()->query('SELECT id, '.$relation[1].' AS value FROM '.$relation[0].' WHERE tstamp>0 ORDER BY value');

        $choices = [];

        while ($objOptions->next()) {
            $choices[$objOptions->id] = $objOptions->value;
        }

        return $choices;
    }
}
