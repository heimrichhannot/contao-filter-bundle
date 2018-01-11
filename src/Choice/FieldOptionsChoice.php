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

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']])) {
            return $choices;
        }

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

        foreach ($options as $option) {
            if (!isset($option['label']) || !isset($option['value'])) {
                continue;
            }

            $choices[$option['label']] = $option['value'];
        }

        return $choices;
    }

    /**
     * Get default contao widget options
     * @param array $element
     * @param array $filter
     * @param array $dca
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
     * Get tag widget options
     * @param array $element
     * @param array $filter
     * @param array $dca
     * @return array
     */
    protected function getTagWidgetOptions(array $element, array $filter, array $dca)
    {
        $options = [];

        /**
         * @var \Codefog\TagsBundle\Manager\ManagerInterface $tagsManager
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