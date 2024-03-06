<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\EventListener;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class FilterConfigElementCallbackListener
{
    public function getValueTypeOptions(DataContainer $dc): array
    {
        $id = $dc->id;
        if (FilterPreselectModel::getTable() === $dc->table) {
            $preselectModel = FilterPreselectModel::findByPk($dc->id);
            if (!$preselectModel) {
                return AbstractType::VALUE_TYPES;
            }
            $id = $preselectModel->element;
        }

        $filterConfigElementModel = FilterConfigElementModel::findByPk($id);
        if (!$filterConfigElementModel) {
            return AbstractType::VALUE_TYPES;
        }

        $choices = AbstractType::VALUE_TYPES;

        $types = System::getContainer()->getParameter('huh.filter')['filter']['types'];
        $typeIndex = array_search($filterConfigElementModel->type, array_column($types, 'name'), true);

        if (!$typeIndex) {
            return $choices;
        }
        $class = $types[$typeIndex]['class'];

        if ($filterConfigElementModel->multiple && defined("$class::VALUE_TYPES_MULTIPLE")) {
            return $class::VALUE_TYPES_MULTIPLE;
        }

        return $class::VALUE_TYPES;
    }
}
