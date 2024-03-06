<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Intl;

class CountryChoice extends FieldOptionsChoice
{
    /**
     * @return array
     */
    protected function collect(): array
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

        [$element, $filter] = $context;

        if ($element->customCountries) {
            $options = $this->getCustomCountryOptions($element, $filter);
        } elseif ($element->customOptions) {
            $options = $this->getCustomOptions($element, $filter);
        } elseif (
            isset($filter['dataContainer'])
            && '' !== $filter['dataContainer']
            && null !== $element->field
        ) {
            Controller::loadDataContainer($filter['dataContainer']);

            if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
                $options = $this->getDcaOptions($element, $filter, $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]);
            }
        }

        $translator = System::getContainer()->get('translator');

        foreach ($options as $key => $option) {
            if (!is_array($option) && (!isset($option['label']) || !isset($option['value']))) {
                $choices[$option] = $key;

                continue;
            }

            if (!$option['value']) {
                continue;
            }

            if ($translator->getCatalogue()->has($option['label'])) {
                $option['label'] = $translator->trans($option['label']);
            }

            $choices[$option['value']] = $option['label'];
        }

        return $choices;
    }

    /**
     * Get custom country options.
     *
     * @return array
     */
    protected function getCustomCountryOptions(FilterConfigElementModel $element, array $filter): array
    {
        if (null === $element->countries) {
            return [];
        }

        $options = StringUtil::deserialize($element->countries, true);

        $all = Countries::getNames();

        return array_intersect_key($all, array_flip($options));
    }
}
