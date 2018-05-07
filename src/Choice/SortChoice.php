<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Sort\AbstractSort;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class SortChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $groupChoices = $this->getContext() instanceof \Contao\DataContainer;

        $config = \System::getContainer()->getParameter('huh.sort');

        if (!isset($config['sort']['classes'])) {
            return $choices;
        }

        foreach ($config['sort']['classes'] as $type) {
            if (!class_exists($type['class'])) {
                continue;
            }

            $r = new \ReflectionClass($type['class']);

            if (!$r->isSubclassOf(AbstractSort::class)) {
                continue;
            }

            if ($groupChoices) {
                $group = $type['type'];
                $choices[$group][] = $type['name'];
                continue;
            }

            $choices[$type['name']] = $type;
        }

        return $choices;
    }
}
