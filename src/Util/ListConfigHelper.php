<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Util;

use Contao\Config;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;

class ListConfigHelper
{
    public static function shareTokenExpiredOrEmpty($entity, $now)
    {
        $shareToken = $entity->shareToken;
        $expirationInterval = StringUtil::deserialize(Config::get('shareExpirationInterval'), true);
        $interval = 604800; // default: 7 days

        if (isset($expirationInterval['unit']) && isset($expirationInterval['value']) && $expirationInterval['value'] > 0) {
            $interval = System::getContainer()->get('huh.utils.date')->getTimePeriodInSeconds($expirationInterval);
        }

        return !$shareToken || !$entity->shareTokenTime || ($entity->shareTokenTime > $now + $interval);
    }

    public static function getFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($filter = System::getContainer()->get('huh.list.list-config-registry')->getFilterByPk($dc->id))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
            ]
        );
    }

    public static function getTextFields(DataContainer $dc)
    {
        if (!$dc->id || null === ($filter = System::getContainer()->get('huh.list.list-config-registry')->getFilterByPk($dc->id))) {
            return [];
        }

        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => $filter['dataContainer'],
                'inputTypes' => ['text'],
            ]
        );
    }
}
