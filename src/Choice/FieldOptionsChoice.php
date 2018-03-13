<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\Translation\Translator;

class FieldOptionsChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];
        $context = $this->getContext();

        if (!isset($context['element']) || !isset($context['filter'])) {
            return $choices;
        }

        $element = $context['element'];
        $filter  = $context['filter'];

        $options = [];

        /** @var Controller $controller */
        $controller = $this->framework->getAdapter(Controller::class);

        if (null === $controller) {
            return $choices;
        }

        $controller->loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']])) {
            return $choices;
        }

        if (true === (bool)$element->customOptions) {
            $options = $this->getCustomOptions($element, $filter);
        } elseif (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            $options = $this->getDcaOptions($element, $filter,
                $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field]);
        }

        /** @var Translator $translator */
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
     * @param FilterConfigElementModel $element
     * @param array $filter
     *
     * @return array
     */
    protected function getCustomOptions(FilterConfigElementModel $element, array $filter)
    {
        if (null === $element->options) {
            return [];
        }

        $options = StringUtil::deserialize($element->options, true);

        return $options;
    }

    /**
     * Get contao dca widget options.
     *
     * @param FilterConfigElementModel $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getDcaOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];
        $dca     = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        if (isset($dca['eval']['isCategoryField']) && $dca['eval']['isCategoryField']) {
            $options = $this->getCategoryWidgetOptions($element, $filter, $dca);

            return $options;
        }

        if (!isset($dca['inputType'])) {
            return $options;
        }

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
     * @param FilterConfigElementModel $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];

        if (!isset($GLOBALS['TL_FFL'][$dca['inputType']])) {
            return $options;
        }

        $class = $GLOBALS['TL_FFL'][$dca['inputType']];

        if (!class_exists($class)) {
            return $options;
        }

        $attributes = Widget::getAttributesFromDca(
            $dca,
            $element->field,
            '',
            $element->field,
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
     * @param FilterConfigElementModel $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getTagWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];

        if (!System::getContainer()->has('codefog_tags.manager_registry')) {
            return $options;
        }

        /**
         * @var $tagsManager \Codefog\TagsBundle\Manager\ManagerInterface
         */
        $tagsManager = System::getContainer()->get('codefog_tags.manager_registry')->get(
            $dca['eval']['tagsManager']
        );

        $tags = $tagsManager->findMultiple();

        if (null === $tags) {
            return $options;
        }

        /** @var \Codefog\TagsBundle\Tag $tag */
        foreach ($tags as $tag) {
            $options[] = ['label' => $tag->getName(), 'value' => $tag->getValue()];
        }

        return $options;
    }

    /**
     * Get category widget options.
     *
     * @param FilterConfigElementModel $element
     * @param array $filter
     * @param array $dca
     *
     * @return array
     */
    protected function getCategoryWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];

        if (!System::getContainer()->has('huh.categories.manager')) {
            return $options;
        }

        if (null === ($categories = System::getContainer()->get('huh.categories.manager')->findByCategoryFieldAndTable($element->field, $filter['dataContainer']))) {
            return $options;
        }

        /** @var \HeimrichHannot\CategoriesBundle\Model\CategoryModel $category */
        foreach ($categories as $category) {
            $options[] = ['label' => $category->frontendTitle ?: $category->title, 'value' => $category->id];
        }

        return $options;
    }
}
