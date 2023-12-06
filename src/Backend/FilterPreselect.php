<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Backend;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\DataContainer\FilterPreselectContainer;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

class FilterPreselect
{
    protected ContaoFramework        $framework;
    private FilterPreselectContainer $filterPreselectContainer;

    public function __construct(ContaoFramework $framework, FilterPreselectContainer $filterPreselectContainer)
    {
        $this->framework = $framework;
        $this->filterPreselectContainer = $filterPreselectContainer;
    }

    /**
     * Adjust label of entries.
     *
     * @param array  $row
     * @param string $label
     *
     * @return string
     */
    public function adjustLabel($row, $label)
    {
        /** @var $filterConfigElement FilterConfigElementModel */
        if (null === ($filterConfigElement = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
                'tl_filter_config_element',
                $row['element']
            ))) {
            return $label;
        }

        $choices = $this->filterPreselectContainer->prepareElementChoices($filterConfigElement);

        switch ($row['initialValueType']) {
            case \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR:
                $label = $choices[$row['initialValue']] ?? $row['initialValue'];

                break;

            case \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY:
                $values = array_map(
                    function ($item) {
                        return $item['value'] ?? null;
                    },
                    StringUtil::deserialize($row['initialValueArray'], true)
                );

                $label = implode(',', array_intersect_key($choices, array_flip(array_filter($values))));

                break;

            case \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_CONTEXTUAL:
                $label = \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_CONTEXTUAL;
        }

        return sprintf('%s -> %s [ID: %s]', $filterConfigElement->title, $label, $filterConfigElement->id);
    }
}
