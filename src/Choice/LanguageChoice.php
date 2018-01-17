<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\System;
use Symfony\Component\Intl\Intl;

class LanguageChoice extends FieldOptionsChoice
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

        if (true === (bool) $element['customLanguages']) {
            $options = $this->getCustomLanguageOptions($element, $filter);
        } elseif (true === (bool) $element['customOptions']) {
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
     * Get custom language options.
     *
     * @param array $element
     * @param array $filter
     *
     * @return array
     */
    protected function getCustomLanguageOptions(array $element, array $filter)
    {
        $options = deserialize($element['languages'], true);

        $all = Intl::getLanguageBundle()->getLanguageNames();

        $options = array_intersect_key($all, array_flip($options));

        return $options;
    }
}
