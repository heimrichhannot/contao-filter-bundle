<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Doctrine\DBAL\FetchMode;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use Symfony\Component\Translation\Translator;

class FieldOptionsChoice extends AbstractChoice
{
    /**
     * @param null $context
     *
     * @return array
     */
    public function getCachedChoices($context = null)
    {
        $choices = parent::getCachedChoices($context);
        $element = $context['element'];

        if ($element->addGroupChoiceField) {
            $groupChoices = $this->getGroupChoicesValue($choices, $element);

            $choices = [System::getContainer()->get('translator')->trans('huh.filter.label.groupChoice') => $groupChoices] + $choices;
        }

        return $choices;
    }

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
        $filter = $context['filter'];

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

        if (true === (bool) $element->customOptions) {
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

            $option['label'] = html_entity_decode($option['label']);

            $choices[$option['label']] = $option['value'];
        }

        if ($element->sortOptionValues) {
            asort($choices);
        }

        if ($element->sortOptionValuesInverted) {
            $choices = array_reverse($choices);
        }

        return $choices;
    }

    /**
     * Get custom options.
     *
     * @param FilterConfigElementModel $element
     * @param array                    $filter
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
     * @param array                    $filter
     * @param array                    $dca
     *
     * @return array
     */
    protected function getDcaOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];
        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        if (isset($dca['eval']['isCategoryField']) && $dca['eval']['isCategoryField']) {
            if (isset($dca['options_callback'])) {
                // TODO: workaround until we have categoryTree in frontend
                $GLOBALS['TL_FFL']['categoryTree'] = 'HeimrichHannot\CategoriesBundle\Widget\CategoryTree';

                return $this->getWidgetOptions($element, $filter, $dca);
            }

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
     * @param array                    $filter
     * @param array                    $dca
     *
     * @return array
     */
    protected function getWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];

        if (!isset($GLOBALS['TL_FFL'][$dca['inputType']]) && (System::getContainer()->get('huh.utils.container')->isBackend() && !isset($GLOBALS['BE_FFL'][$dca['inputType']]))) {
            return $options;
        }

        $class = $GLOBALS['TL_FFL'][$dca['inputType']] ?? $GLOBALS['BE_FFL'][$dca['inputType']] ?? null;

        if (null === $class || !class_exists($class)) {
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

        if (\is_array($attributes['options'])) {
            $options = $attributes['options'];
        }

        // cleanup/revise options (remove options that do not occur result list)
        if (true === (bool) $element->reviseOptions && !empty($options) && isset($dca['foreignKey']) && !isset($dca['options']) && !isset($dca['options_callback'])) {
            if (null !== ($filterQueryBuilder = System::getContainer()->get('huh.filter.manager')->getQueryBuilder($filter['id'], [$element->id]))) {
                $filterQueryBuilder->select([$filter['dataContainer'].'.'.$element->field]);
                $filterQueryBuilder->groupBy($filter['dataContainer'].'.'.$element->field);

                $ids = $filterQueryBuilder->execute()->fetchAll(FetchMode::COLUMN, 0);

                if (!empty($ids)) {
                    foreach ($options as $key => $option) {
                        if (!\in_array($option['value'], $ids)) {
                            unset($options[$key]);
                        }
                    }
                }
            }
        }

        if (!empty($options) && true === (bool) $element->adjustOptionLabels && !empty($element->optionLabelPattern)) {
            if (null !== ($filterQueryBuilder = System::getContainer()->get('huh.filter.manager')->getQueryBuilder($filter['id'], [$element->id]))) {
                $filterQueryBuilder->select([$filter['dataContainer'].'.'.$element->field, $filter['dataContainer'].'.*']);
                $filterQueryBuilder->orderBy($element->field);
                $rows = $filterQueryBuilder->execute()->fetchAll();

                $data = [];

                foreach ($rows as $row) {
                    $currentValue = $row[$element->field];

                    if (isset($data[$currentValue])) {
                        $data[$currentValue]['count'] += 1;

                        continue;
                    }

                    $data[$currentValue] = ['data' => $row, 'count' => 1];
                }

                foreach ($options as $key => &$option) {
                    if (!isset($option['label']) || !isset($rows[$option['value']])) {
                        continue;
                    }

                    $params = $data[$option['value']];
                    $params['label'] = $option['label'];

                    foreach ($params as $key => $value) {
                        unset($params[$key]);
                        $params['%'.$key.'%'] = $value;
                    }

                    $option['label'] = System::getContainer()->get('translator')->trans($element->optionLabelPattern, $params);
                }
            }
        }

        return $options;
    }

    /**
     * Get tag widget options.
     *
     * @param FilterConfigElementModel $element
     * @param array                    $filter
     * @param array                    $dca
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
         * @var \Codefog\TagsBundle\Manager\ManagerInterface
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
     * @param array                    $filter
     * @param array                    $dca
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

    /**
     * @param array                    $choices
     * @param FilterConfigElementModel $element
     *
     * @return string
     */
    protected function getGroupChoicesValue(array $choices, FilterConfigElementModel $element): string
    {
        if ($element->modifyGroupChoices) {
            $choices = array_intersect($choices, StringUtil::deserialize($element->groupChoices, true));
        }

        return implode(',', $choices);
    }
}
