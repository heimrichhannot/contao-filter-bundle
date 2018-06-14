<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class TypeChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];
        $filterType = 'filter';

        $groupChoices = $this->getContext() instanceof \Contao\DataContainer;

        $config = \System::getContainer()->getParameter('huh.filter');

        if (!isset($config['filter']['types'])) {
            return $choices;
        }

        if ($groupChoices && null !== ($pid = $this->framework->getAdapter(FilterConfigElementModel::class)->findById($this->context->id)->pid)) {
            if (null !== ($type = $this->framework->getAdapter(FilterConfigModel::class)->findById($pid)->type)) {
                $filterType = $type;
            }
        }

        foreach ($config[$filterType]['types'] as $type) {
            if (!class_exists($type['class'])) {
                continue;
            }

            if (!is_subclass_of($type['class'], AbstractType::class)) {
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
