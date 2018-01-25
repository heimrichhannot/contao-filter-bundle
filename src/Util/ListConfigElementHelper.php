<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\DataContainer;
use Contao\System;

class ListConfigElementHelper
{
    public static function getFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($filter = System::getContainer()->get('huh.list.list-config-element-registry')->getFilterByPk($dc->id))) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
            ]
        );
    }

    public static function getCheckboxFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($filter = System::getContainer()->get('huh.list.list-config-element-registry')->getFilterByPk($dc->id))) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
                'inputTypes' => ['checkbox'],
            ]
        );
    }
}
