<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class TypeChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $groupChoices = $this->getContext() instanceof \Contao\DataContainer;

        $config = \System::getContainer()->getParameter('huh.filter');

        if (!isset($config['filter']['types'])) {
            return $choices;
        }

        foreach ($config['filter']['types'] as $type) {
            if (!class_exists($type['class'])) {
                continue;
            }

            if ($groupChoices) {
                $group = $type['type'];
                $choices[$group][] = $type['name'];
                continue;
            }

            $choices[$type['name']] = $type['class'];
        }

        return $choices;
    }
}
