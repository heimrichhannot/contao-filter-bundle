<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\System;
use Contao\Widget;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;

class FieldOptionsChoice extends AbstractChoice
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

        if (true === (bool) $element['customOptions']) {
            $options = $this->getCustomOptions($element, $filter);
        } elseif (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']])) {
            $options = $this->getDcaOptions($element, $filter, $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']]);
        }

        $translator = System::getContainer()->get('translator');

        foreach ($options as $option) {
            if (!isset($option['label']) || !isset($option['value'])) {
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
     * Get custom options.
     *
     * @param array $element
     * @param array $filter
     *
     * @return array
     */
    protected function getCustomOptions(array $element, array $filter)
    {
        $options = deserialize($element['options'], true);

        return $options;
    }

    /**
     * Get contao dca widget options.
     *
     * @param array $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getDcaOptions(array $element, array $filter, array $dca)
    {
        $options = [];
        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']];

        switch ($dca['inputType']) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $options = $this->getTagWidgetOptions($element, $filter, $dca);
                break;
            default:
                $options = $this->getWidgetOptions($element, $filter, $dca);
        }

        return $options;
    }

    /**
     * Get default contao widget options.
     *
     * @param array $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getWidgetOptions(array $element, array $filter, array $dca)
    {
        $options = [];

        $class = $GLOBALS['TL_FFL'][$dca['inputType']];

        if (!class_exists($class)) {
            return $options;
        }

        $attributes = Widget::getAttributesFromDca(
            $dca,
            $element['field'],
            '',
            $element['field'],
            $filter['dataContainer'],
            null
        );

        if (is_array($attributes['options'])) {
            $options = $attributes['options'];
        }

        return $options;
    }

    /**
     * Get tag widget options.
     *
     * @param array $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getTagWidgetOptions(array $element, array $filter, array $dca)
    {
        $options = [];

        /**
         * @var \Codefog\TagsBundle\Manager\ManagerInterface
         */
        $tagsManager = System::getContainer()->get('codefog_tags.manager_registry')->get(
            $dca['eval']['tagsManager']
        );

        $tags = $tagsManager->findMultiple();

        /** @var \Codefog\TagsBundle\Tag $tag */
        foreach ($tags as $tag) {
            $options[] = ['label' => $tag->getName(), 'value' => $tag->getValue()];
        }

        return $options;
    }
}
