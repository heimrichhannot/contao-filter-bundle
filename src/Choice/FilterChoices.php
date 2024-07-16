<?php

namespace HeimrichHannot\FilterBundle\Choice;

use Codefog\TagsBundle\Manager\ManagerInterface;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Intl\Countries;

class FilterChoices
{
    /**
     * @param FilterConfigElementModel $element
     * @param array|null $filter
     * @return array
     */
    public static function getCountryOptions(FilterConfigElementModel $element, array|null $filter): array
    {
        $choices = [];
        $options = [];

        $table = ($filter['dataContainer'] ?? null) ?: null;

        if ($element->customCountries)
        {
            if (null !== $element->countries)
            {
                $countries = StringUtil::deserialize($element->countries, true);
                $all = Countries::getNames();
                $options = array_intersect_key($all, array_flip($countries));
            }
        }
        elseif ($element->customOptions)
        {
            $options = $element->options ? StringUtil::deserialize($element->options, true) : [];
        }
        elseif ($table && null !== $element->field)
        {
            Controller::loadDataContainer($table);

            if (isset($GLOBALS['TL_DCA'][$table]['fields'][$element->field])) {
                $options = static::getDcaOptions($element, $filter, $GLOBALS['TL_DCA'][$table]['fields'][$element->field]);
            }
        }

        $translator = System::getContainer()->get('translator');

        foreach ($options as $key => $option)
        {
            if (!is_array($option) && (!isset($option['label']) || !isset($option['value'])))
            {
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
     * Get contao dca widget options.
     *
     * @param FilterConfigElementModel $element
     * @param array $filter
     * @param array $dca
     * @return array
     */
    protected static function getDcaOptions(FilterConfigElementModel $element, array $filter, array $dca): array
    {
        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];

        if (isset($dca['eval']['isCategoryField']) && $dca['eval']['isCategoryField']) {
            if (isset($dca['options_callback'])) {
                // TODO: workaround until we have categoryTree in frontend
                $GLOBALS['TL_FFL']['categoryTree'] = 'HeimrichHannot\CategoriesBundle\Widget\CategoryTree';

                return static::getWidgetOptions($element, $filter, $dca);
            }

            return static::getCategoryWidgetOptions($element, $filter, $dca);
        }

        if (!isset($dca['inputType'])) {
            return [];
        }

        return match ($dca['inputType'])
        {
            'cfgTags' => isset($dca['eval']['tagsManager'])
                ? static::getTagWidgetOptions($element, $filter, $dca)
                : [],
            default => static::getWidgetOptions($element, $filter, $dca),
        };
    }

    /**
     * Get default contao widget options.
     *
     * @param FilterConfigElementModel $element
     * @param array $filter
     * @param array $dca
     * @return array
     */
    protected static function getWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca): array
    {
        $class = $GLOBALS['TL_FFL'][$dca['inputType']] ?? $GLOBALS['BE_FFL'][$dca['inputType']] ?? '';

        if (!class_exists($class)) {
            return [];
        }

        $attributes = Widget::getAttributesFromDca(
            $dca,
            $element->field,
            '',
            $element->field,
            $filter['dataContainer']
        );

        $options = [];

        if (is_array($attributes['options'])) {
            $options = $attributes['options'];
        }

        if ($element->dynamicOptions)
        {
            $options = [];

            $queryBuilder = System::getContainer()->get('huh.filter.manager')
                ->getInitialQueryBuilder($filter['id'], [$element->id], true);

            if (null !== $queryBuilder)
            {
                $items = $queryBuilder->select([$filter['dataContainer'].'.'.$element->field])->execute()->fetchFirstColumn();

                // make the values unique
                $items = array_filter(array_unique($items));

                if (($dca['eval']['multiple'] ?? false) === true) {
                    $multipleItems = [];

                    foreach ($items as $item) {
                        $multipleItems = array_merge($multipleItems, StringUtil::deserialize($item, true));
                    }
                    $items = array_unique($multipleItems);
                }

                if (isset($dca['foreignKey'])) {
                    [$foreignTable, $foreignField] = explode('.', $dca['foreignKey']);

                    if (!empty($items) && null !== ($instances = System::getContainer()->get(Utils::class)->model()->findModelInstancesBy(
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
        }
        elseif ($element->reviseOptions && !empty($options)) // cleanup/revise options (remove options that do not occur result list)
        {
            $filterQueryBuilder = System::getContainer()->get('huh.filter.manager')
                ->getInitialQueryBuilder($filter['id'], [$element->id], true);

            if (null !== $filterQueryBuilder)
            {
                $filterQueryBuilder->select([$filter['dataContainer'].'.'.$element->field]);

                $values = $filterQueryBuilder->executeQuery()->fetchFirstColumn();

                // make the values unique
                $values = array_unique($values);

                foreach ($options as $key => $option) {
                    if (!in_array($option['value'], $values)) {
                        unset($options[$key]);
                    }
                }
            }
        }

        if (!empty($options) && $element->adjustOptionLabels && !empty($element->optionLabelPattern))
        {
            $filterQueryBuilder = System::getContainer()->get('huh.filter.manager')
                ->getQueryBuilder($filter['id'], [$element->id]);

            if (null === $filterQueryBuilder)
            {
                return $options;
            }

            $rows = $filterQueryBuilder
                ->select([$filter['dataContainer'].'.'.$element->field, $filter['dataContainer'].'.*'])
                ->orderBy($element->field)
                ->executeQuery()
                ->fetchAllAssociative();

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

                $option['label'] = System::getContainer()->get('translator')
                    ->trans($element->optionLabelPattern, $params);
            }
        }

        return $options;
    }

    /**
     * Get tag widget options.
     */
    protected static function getTagWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca): array
    {
        if (!System::getContainer()->has('codefog_tags.manager_registry')) {
            return [];
        }

        /**
         * @var ManagerInterface $tagsManager
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
            return [];
        }

        $options = [];
        /** @var \Codefog\TagsBundle\Tag $tag */
        foreach ($tags as $tag) {
            $options[] = ['label' => $tag->getName(), 'value' => $tag->getValue()];
        }

        return $options;
    }


    /**
     * Get category widget options.
     */
    protected static function getCategoryWidgetOptions(FilterConfigElementModel $element, array $filter, array $dca): array
    {
        if (!System::getContainer()->has('huh.categories.manager')) {
            return [];
        }

        $categories = System::getContainer()->get('huh.categories.manager')
            ->findByCategoryFieldAndTable($element->field, $filter['dataContainer']);

        if (null === $categories) {
            return [];
        }

        $options = [];
        /** @var \HeimrichHannot\CategoriesBundle\Model\CategoryModel $category */
        foreach ($categories as $category) {
            $options[] = ['label' => $category->frontendTitle ?: $category->title, 'value' => $category->id];
        }

        return $options;
    }

    public static function getElementOptions(int|string $pid, ?array $types = null): array
    {
        if ($types === null) {
            $types = [];
        }

        if (!is_numeric($pid) || $pid < 1) {
            return [];
        }

        /** @var ContaoFramework $framework */
        $framework = System::getContainer()->get('contao.framework');
        /** @var FilterConfigElementModel */
        $adapter = $framework->getAdapter(FilterConfigElementModel::class);
        $elements = $adapter->findPublishedByPidAndTypes($pid, $types);

        if (null === $elements) {
            return [];
        }

        $choices = [];

        foreach ($elements as $element) {
            $choices[$element->id] = $element->title.' ['.$element->type.']';
        }

        return $choices;
    }
}