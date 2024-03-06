<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\System;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;
use Psr\Cache\InvalidArgumentException;

class TemplateChoice extends AbstractChoice
{
    /**
     * @return array
     * @throws InvalidArgumentException
     */
    protected function collect(): array
    {
        $choices = [];
        $config = System::getContainer()->getParameter('huh.filter');

        if (isset($config['filter']['template_prefixes'])) {
            $this->setContext($config['filter']['template_prefixes']);
            $choices = $this->getCachedChoices();
            // $choices = System::getContainer()->get('huh.utils.choice.twig_template')->setContext($config['filter']['template_prefixes'])->getCachedChoices();
        }

        if (isset($config['filter']['templates'])) {
            foreach ($config['filter']['templates'] as $template) {
                // remove duplicates returned by `huh.utils.choice.twig_template`
                if (false !== ($idx = array_search($template['template'], $choices, true))) {
                    unset($choices[$idx]);
                }

                $choices[$template['name']] = $template['template'].' (Yaml)';
            }
        }

        asort($choices);

        return $choices;
    }
}
