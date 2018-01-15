<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\System;
use Contao\Widget;
use Symfony\Component\Intl\Intl;

class CountryChoice extends FieldOptionsChoice
{

    /**
     * @return array
     */
    protected function collect()
    {
        list($element, $filter) = $this->getContext();

        $choices = [];
        $options = [];

        \Controller::loadDataContainer($filter['dataContainer']);

        if (true === (bool)$element['customCountries']) {
            $options = $this->getCustomCountryOptions($element, $filter);
        } elseif (true === (bool)$element['customOptions']) {
            $options = $this->getCustomOptions($element, $filter);
        } elseif (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']])) {
            $options = $this->getDcaOptions($element, $filter, $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']]);
        }

        $translator = System::getContainer()->get('translator');

        foreach ($options as $key => $option) {
            if (!is_array($option) && (!isset($option['label']) || !isset($option['value']))) {
                $choices[$option] = $key;
                continue;
            }

            if ($translator->getCatalogue()->has($option['label'])) {
                $option['label'] = $translator->trans($option['label']);
            }

            $choices[$option['label']] = $option['value'];
        }

        return $choices;
    }

    /**
     * Get custom country options
     * @param array $element
     * @param array $filter
     * @return array
     */
    protected function getCustomCountryOptions(array $element, array $filter)
    {
        $options = deserialize($element['countries'], true);

        $all = Intl::getRegionBundle()->getCountryNames();

        $options = array_intersect_key($all, array_flip($options));

        return $options;
    }
}