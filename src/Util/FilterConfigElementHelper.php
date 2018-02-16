<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use Contao\DataContainer;
use Contao\System;

class FilterConfigElementHelper
{
    public static function getFields(DataContainer $dc)
    {
        if (null === ($model = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return [];
        }

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.registry')->findById($model->pid))) {
            return [];
        }

        return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filterConfig->getFilter()['dataContainer'],
            ]
        );
    }
}
