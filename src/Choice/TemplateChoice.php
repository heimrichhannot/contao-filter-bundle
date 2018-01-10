<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;
use Symfony\Component\Form\AbstractType;

class TemplateChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];

        $config = \System::getContainer()->getParameter('huh');

        if (!isset($config['filter']['templates'])) {
            return $choices;
        }

        $choices = $config['filter']['templates'];

        return $choices;
    }
}