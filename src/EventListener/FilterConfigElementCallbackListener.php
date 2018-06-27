<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Contao\DC_Table;
use HeimrichHannot\FilterBundle\Filter\AbstractType;

class FilterConfigElementCallbackListener
{
    /**
     * @param DC_Table $dca
     *
     * @return array
     */
    public function getValueTypeOptions(DC_Table $dca)
    {
        $choices = AbstractType::VALUE_TYPES;
        $filter = $dca->activeRecord->fetchAllAssoc()[0];
        if (empty($filter)) {
            return $choices;
        }
        $types = \Contao\System::getContainer()->getParameter('huh.filter')['filter']['types'];
        $typeIndex = array_search($filter['type'], array_column($types, 'name'), true);
        if (!$typeIndex) {
            return $choices;
        }
        $class = $types[$typeIndex]['class'];

        return $class::VALUE_TYPES;
    }
}
