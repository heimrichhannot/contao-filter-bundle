<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class FilterConfigElementCallbackListener
{
    /**
     * @return array
     */
    public function getValueTypeOptions(DataContainer $dc)
    {
        $choices = AbstractType::VALUE_TYPES;

        if (null === ($filter = System::getContainer()->get(ModelUtil::class)->findModelInstanceByPk('tl_filter_config', $dc->id))) {
            return $choices;
        }

        $types = \Contao\System::getContainer()->getParameter('huh.filter')['filter']['types'];
        $typeIndex = array_search($filter->type, array_column($types, 'name'), true);

        if (!$typeIndex) {
            return $choices;
        }
        $class = $types[$typeIndex]['class'];

        return $class::VALUE_TYPES;
    }
}
