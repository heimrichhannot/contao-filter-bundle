<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

class FilterCollection
{
    private array         $bundleConfig;
    private FilterManager $filterManager;

    public function __construct(array $bundleConfig, FilterManager $filterManager)
    {
        $this->bundleConfig = $bundleConfig;
        $this->filterManager = $filterManager;
    }

    /**
     * @param FilterConfigElementModel|int $filterConfigElement
     */
    public function getFilterTypeById($filterConfigElement, array $options = []): ?AbstractType
    {
        $options = array_merge([
            'published' => false,
        ], $options);

        if (is_int($filterConfigElement)) {
            $filterConfigElement = FilterConfigElementModel::findById($filterConfigElement);
        }

        if (!($filterConfigElement instanceof FilterConfigElementModel)) {
            return null;
        }

        if ($options['published'] && !$filterConfigElement->published) {
            return null;
        }

        $class = $this->getClassByType($filterConfigElement->type);

        if (null === $class) {
            return null;
        }

        if (null === ($filter = $this->filterManager->findById($filterConfigElement->pid))) {
            return null;
        }

        return new $class($filter);
    }

    public function getFilterElementTypes(string $filterType = 'filter', bool $group = false, $context = []): array
    {
        if (!isset($this->bundleConfig[$filterType]['types'])) {
            return [];
        }

        $choices = [];

        foreach ($this->bundleConfig[$filterType]['types'] as $type) {
            if (!class_exists($type['class'])) {
                trigger_error(sprintf('Warning: Class %s does not exist.', $type['class']), \E_USER_WARNING);

                continue;
            }

            if (!is_subclass_of($type['class'], AbstractType::class)) {
                continue;
            }

            if (!$type['class']::isEnabledForCurrentContext($context)) {
                continue;
            }

            if ($group) {
                $choiceGroup = $type['type'];
                $choices[$choiceGroup][] = $type['name'];

                continue;
            }

            $choices[$type['name']] = $type;
        }

        return $choices;
    }

    /**
     * @return class-string<AbstractType>|string|null
     */
    public function getClassByType(string $type): ?string
    {
        $class = null;

        if (!isset($this->bundleConfig['filter']['types']) || !is_array($this->bundleConfig['filter']['types'])) {
            return $class;
        }

        foreach ($this->bundleConfig['filter']['types'] as $filterType) {
            if (isset($filterType['name']) && $filterType['name'] === $type && isset($filterType['class'])) {
                $class = $filterType['class'];

                break;
            }
        }

        return $class;
    }
}
