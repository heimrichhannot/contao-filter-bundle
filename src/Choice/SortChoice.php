<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Sort\AbstractSort;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;

class SortChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect(): array
    {
        $choices = [];

        $groupChoices = $this->getContext() instanceof DataContainer;

        $config = System::getContainer()->getParameter('huh.sort');

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
