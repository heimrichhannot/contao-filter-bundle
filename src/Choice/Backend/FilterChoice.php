<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice\Backend;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;
use HeimrichHannot\FilterBundle\Registry\FilterRegistry;

class FilterChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collectChoices()
    {
        /**
         * @var $registry FilterRegistry
         */
        $registry = \System::getContainer()->get('huh.filter.registry');

        return $registry->getGroupedAliases();
    }
}