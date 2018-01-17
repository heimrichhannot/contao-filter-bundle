<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class TemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = \System::getContainer()->getParameter('huh.filter');

        if (!isset($config['filter']['templates'])) {
            return $choices;
        }

        $templates = $config['filter']['templates'];

        foreach ($templates as $config) {
            $choices[$config['name']] = $config['template'];
        }

        return $choices;
    }
}
