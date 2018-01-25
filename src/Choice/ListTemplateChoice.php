<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\System;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class ListTemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['templates']['list'])) {
            return $choices;
        }

        foreach ($config['list']['templates']['list'] as $template) {
            $choices[$template['name']] = $template['template'];
        }

        asort($choices);

        return $choices;
    }
}
