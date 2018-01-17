<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\Form\AbstractType;

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

        foreach ($templates as $config)
        {
            $choices[$config['name']] = $config['template'];
        }

        return $choices;
    }
}