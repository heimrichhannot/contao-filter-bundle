<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

class TypeChoice extends AbstractChoice
{

    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = \System::getContainer()->getParameter('huh');

        if (!isset($config['filter']['types'])) {
            return $choices;
        }

        foreach ($config['filter']['types'] as $name => $class) {
            if (!class_exists($class)) {
                continue;
            }

            $choices[$name] = $class;
        }

        return $choices;
    }
}