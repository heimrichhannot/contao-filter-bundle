<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Backend;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use HeimrichHannot\FilterBundle\DataContainer\FilterPreselectContainer;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class FilterPreselect
{
    protected ContaoFramework        $framework;
    private FilterPreselectContainer $filterPreselectContainer;
    private Utils                    $utils;

    public function __construct(
        ContaoFramework $framework,
        FilterPreselectContainer $filterPreselectContainer,
        Utils $utils
    ) {
        $this->framework = $framework;
        $this->filterPreselectContainer = $filterPreselectContainer;
        $this->utils = $utils;
    }

    /**
     * Adjust label of entries.
     *
     * @param array $row
     * @param string $label
     *
     * @return string
     */
    public function adjustLabel(array $row, string $label): string
    {
        /** @var $filterConfigElement FilterConfigElementModel */
        $filterConfigElement = $this->utils->model()
            ->findModelInstanceByPk('tl_filter_config_element', $row['element']);
        if (null === $filterConfigElement) {
            return $label;
        }

        $choices = $this->filterPreselectContainer->prepareElementChoices($filterConfigElement);

        switch ($row['initialValueType']) {
            case AbstractType::VALUE_TYPE_SCALAR:
                $label = $choices[$row['initialValue']] ?? $row['initialValue'];

                break;

            case AbstractType::VALUE_TYPE_ARRAY:
                $values = array_map(
                    function ($item) {
                        return $item['value'] ?? null;
                    },
                    StringUtil::deserialize($row['initialValueArray'], true)
                );

                $label = implode(',', array_intersect_key($choices, array_flip(array_filter($values))));

                break;

            case AbstractType::VALUE_TYPE_CONTEXTUAL:
                $label = AbstractType::VALUE_TYPE_CONTEXTUAL;
        }

        return sprintf('%s -> %s [ID: %s]', $filterConfigElement->title, $label, $filterConfigElement->id);
    }
}
