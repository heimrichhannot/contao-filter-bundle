<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Intl\Intl;

class CountryChoice extends FieldOptionsChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];
        $options = [];

        if (!is_array($this->getContext()) || empty($this->getContext())) {
            return $choices;
        }

        $context = $this->getContext();

        if (!isset($context[0]) || !isset($context[1])) {
            return $choices;
        }

        list($element, $filter) = $context;

        if (true === (bool)$element->customCountries) {
            $options = $this->getCustomCountryOptions($element, $filter);
        } elseif (true === (bool)$element->customOptions) {
            $options = $this->getCustomOptions($element, $filter);
        } elseif (isset($filter['dataContainer']) && '' !== $filter['dataContainer'] && null !== $element->field) {
            if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
                \Controller::loadDataContainer($filter['dataContainer']);
                $options = $this->getDcaOptions($element, $filter, $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]);
            }
        }

        $translator = System::getContainer()->get('translator');

        foreach ($options as $key => $option) {
            if (!is_array($option) && (!isset($option['label']) || !isset($option['value']))) {
                $choices[$option] = $key;
                continue;
            }

            if ($translator->getCatalogue()->has($option['label'])) {
                $option['label'] = $translator->trans($option['label']);
            } elseif (null !== ($label = Intl::getRegionBundle()->getCountryName($option['label']))) {
                $option['label'] = $label;
            }

            $choices[$option['label']] = $option['value'];
        }

        return $choices;
    }

    /**
     * Get custom country options.
     *
     * @param FilterConfigElementModel $element
     * @param array                    $filter
     *
     * @return array
     */
    protected function getCustomCountryOptions(FilterConfigElementModel $element, array $filter)
    {
        if (null === $element->countries) {
            return [];
        }

        $options = StringUtil::deserialize($element->countries, true);

        $all = Intl::getRegionBundle()->getCountryNames();

        $options = array_intersect_key($all, array_flip($options));

        return $options;
    }
}
