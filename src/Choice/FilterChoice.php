<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;


class FilterChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collectChoices()
    {
        $registry = \System::getContainer()->get('huh.news.list_filter.registry');

    }
}