<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;

class TypeChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect(): array
    {
        $choices = [];
        $filterType = 'filter';

        $groupChoices = $this->getContext() instanceof DataContainer;

        $config = System::getContainer()->getParameter('huh.filter');

        if (!isset($config['filter']['types'])) {
            return $choices;
        }

        if (null !== $this->getContext() && is_object($this->getContext()) && null !== $this->getContext()->id && null !== ($filterConfigElement = $this->framework->getAdapter(FilterConfigElementModel::class)->findById($this->getContext()->id)) && $filterConfigElement->pid > 0) {
            if (null !== ($filterConfig = $this->framework->getAdapter(FilterConfigModel::class)->findById($filterConfigElement->pid)) && $filterConfig->type) {
                $filterType = $filterConfig->type;
            }
        }

        foreach ($config[$filterType]['types'] as $type) {
            if (!class_exists($type['class'])) {
                trigger_error(sprintf('Warning: Class %s does not exist.', $type['class']), E_USER_WARNING);

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
