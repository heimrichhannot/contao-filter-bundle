<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

class ParentType extends ChoiceType
{
    /** {@inheritdoc} */
    public function getChoices(FilterConfigElementModel $element)
    {
        $context = [];
        $filter = $this->config->getFilter();

        if (!isset($filter['dataContainer'])) {
            return [];
        }

        $context['dataContainer'] = $table = $filter['dataContainer'];
        $parentTable = null;

        switch ($table) {
            case 'tl_member':
                $parentTable = 'tl_member_group';

                break;
            default:
                Controller::loadDataContainer($table);

                if (isset($GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'])) {
                    $foreignKey = explode('.', $GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey']);
                    $parentTable = $foreignKey[0];
                }
                break;
        }

        if (null === $parentTable) {
            return [];
        }

        Controller::loadDataContainer($parentTable);

        $choices = System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
            'dataContainer' => $parentTable,
        ]);

        return array_flip($choices);
    }
}
