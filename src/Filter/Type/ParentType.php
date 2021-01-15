<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

class ParentType extends ChoiceType
{
    const TYPE = 'parent';

    /** {@inheritdoc} */
    public function getChoices(FilterConfigElementModel $element)
    {
        $choices = [];
        $context = [];
        $filter = $this->config->getFilter();

        if (!isset($filter['dataContainer'])) {
            return $choices;
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

        if (System::getContainer()->has('huh.utils.choice.model_instance')) {
            $choices = System::getContainer()->get('huh.utils.choice.model_instance')->getCachedChoices([
                'dataContainer' => $parentTable,
                'labelPattern' => '%title% [ID: %id%]',
            ]);
        }

        return $choices;
    }
}
