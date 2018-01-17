<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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
                $group             = $type['type'];
                $choices[$group][] = $type['name'];
                continue;
            }

            $choices[$type['name']] = $type['class'];
        }

        return $choices;
    }
}