<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
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
use HeimrichHannot\FilterBundle\Type\FilterTypeCollection;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
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
        $controller->loadLanguageFile($filter['dataContainer']);

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

            $choices[$option['value']] = $option['label'];
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
     * @return array
     */
    protected function getCustomOptions(FilterConfigElementModel $element, array $filter)
    {
        if (null === $element->options) {
            return [];
        }

        $options = StringUtil::deserialize($element->options, true);

        return $this->adjustOptionLabels($element, $filter, $options);
    }

    /**
     * Get contao dca widget options.
     *
     * @return array
     */
    protected function getDcaOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];

        if (isset($dca['eval']['isCategoryField']) && $dca['eval']['isCategoryField']) {
            if (isset($dca['options_callback'])) {
                // TODO: workaround until we have categoryTree in frontend
                $GLOBALS['TL_FFL']['categoryTree'] = 'HeimrichHannot\CategoriesBundle\Widget\CategoryTree';

                return $this->getWidgetOptions($element, $filter, $dca);
            }

            $options = $this->getCategoryWidgetOptions($element, $filter, $dca);

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
     * @return array
     */
    protected function getWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca)
    {
        $options = [];

        $filterTypeCollection = System::getContainer()->get(FilterTypeCollection::class);

        // fix for new filter types to show possible choices if inputType dca attribute is not given
        if ($filterTypeCollection->hasType($element->type)) {
            $dca['inputType'] = $element->inputType;
        }

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

        if (true === (bool) $element->dynamicOptions) {
            $options = [];

            if (null !== ($queryBuilder = System::getContainer()->get('huh.filter.manager')->getInitialQueryBuilder($filter['id'], [$element->id], true))) {
                $items = $queryBuilder->select([$filter['dataContainer'].'.'.$element->field])->execute()->fetchAll(FetchMode::COLUMN, 0);

                // make the values unique
                $items = array_unique($items);

                if (isset($dca['foreignKey'])) {
                    [$foreignTable, $foreignField] = explode('.', $dca['foreignKey']);

                    if (null !== ($instances = System::getContainer()->get(ModelUtil::class)->findModelInstancesBy(
                        $foreignTable, [$foreignTable.'.id IN ('.implode(',', $items).')'], []))) {
                        $labels = array_combine($instances->fetchEach('id'), $instances->fetchEach($foreignField));

                        foreach ($items as $item) {
                            $options[] = [
                                'value' => $item,
                                'label' => $labels[$item] ?? $item,
                            ];
                        }
                    }
                } elseif (isset($dca['reference'])) {
                    foreach ($items as $item) {
                        $options[] = [
                            'value' => $item,
                            'label' => $dca['reference'][$item] ?? $item,
                        ];
                    }
                } else {
                    foreach ($items as $item) {
                        $options[] = [
                            'value' => $item,
                            'label' => $item,
                        ];
                    }
                }
            }
        } else {
            // cleanup/revise options (remove options that do not occur result list)
            if (true === (bool) $element->reviseOptions && !empty($options)) {
                if (null !== ($filterQueryBuilder = System::getContainer()->get('huh.filter.manager')->getInitialQueryBuilder($filter['id'], [$element->id], true))) {
                    $filterQueryBuilder->select([$filter['dataContainer'].'.'.$element->field]);

                    $values = $filterQueryBuilder->execute()->fetchAll(FetchMode::COLUMN, 0);

                    // make the values unique
                    $values = array_unique($values);

                    foreach ($options as $key => $option) {
                        if (!\in_array($option['value'], $values)) {
                            unset($options[$key]);
                        }
                    }
                }
            }
        }

        return $this->adjustOptionLabels($element, $filter, $options);
    }

    /**
     * Get tag widget options.
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

        if (method_exists($tagsManager, 'findMultiple')) {
            $tags = $tagsManager->findMultiple();
        } else {
            $tags = $tagsManager->getAllTags();
        }

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

    protected function getGroupChoicesValue(array $choices, FilterConfigElementModel $element): string
    {
        if ($element->modifyGroupChoices) {
            $choices = array_intersect($choices, StringUtil::deserialize($element->groupChoices, true));
        }

        return implode(',', $choices);
    }

    private function adjustOptionLabels(FilterConfigElementModel $element, array $filter, array $options): array
    {
        if (!empty($options) && true === (bool) $element->adjustOptionLabels && !empty($element->optionLabelPattern)) {
            if (null !== ($filterQueryBuilder = System::getContainer()->get('huh.filter.manager')->getQueryBuilder($filter['id'], [$element->id]))) {
                $filterQueryBuilder->select([$filter['dataContainer'].'.'.$element->field, $filter['dataContainer'].'.*']);
                $filterQueryBuilder->orderBy($element->field);
                $rows = $filterQueryBuilder->execute()->fetchAll();

                $data = [];

                foreach ($rows as $row) {
                    $currentValue = $row[$element->field];

                    if (isset($data[$currentValue])) {
                        ++$data[$currentValue]['count'];

                        continue;
                    }

                    $data[$currentValue] = ['data' => $row, 'count' => 1];
                }

                foreach ($options as &$option) {
                    if (!isset($option['label']) || !isset($rows[$option['value']])) {
                        continue;
                    }

                    $params = $data[$option['value']];
                    $params['label'] = $option['label'];

                    foreach ($params as $key => $value) {
                        unset($params[$key]);
                        $params['%'.$key.'%'] = $value;
                    }

                    if (!$params['%count%']) {
                        $params['%count%'] = 0;
                    }

                    $option['label'] = System::getContainer()->get('translator')->trans($element->optionLabelPattern, $params);
                }
            }
        }

        return $options;
    }
}
