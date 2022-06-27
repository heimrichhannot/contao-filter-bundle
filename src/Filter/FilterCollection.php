<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
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

        if (\is_int($filterConfigElement)) {
            $filterConfigElement = FilterConfigElementModel::findById($filterConfigElement);
        }

        if (!($filterConfigElement instanceof FilterConfigElementModel)) {
            return null;
        }

        if ($options['published'] && !$filterConfigElement->published) {
            return null;
        }

        $class = null;

        if (!isset($this->bundleConfig['filter']['types']) || !\is_array($this->bundleConfig['filter']['types'])) {
            return null;
        }

        foreach ($this->bundleConfig['filter']['types'] as $type) {
            if (isset($type['name']) && $type['name'] === $filterConfigElement->type && isset($type['class'])) {
                $class = $type['class'];

                break;
            }
        }

        if (null === $class) {
            return null;
        }

        if (null === ($filter = $this->filterManager->findById($filterConfigElement->pid))) {
            return null;
        }

        return new $class($filter);
    }
}
