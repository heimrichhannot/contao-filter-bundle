<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;
use DeepCopy\Filter\Filter;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;

class FilterPreselectUtil
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Get preselected query builder based on given preselection.
     *
     * @param int                    $id            The filter id
     * @param FilterQueryBuilder     $queryBuilder  The query builder
     * @param FilterPreselectModel[] $preselections list of preselections
     *
     * @return FilterQueryBuilder The modified query builder
     */
    public function getPreselectQueryBuilder(int $id, FilterQueryBuilder $queryBuilder, array $preselections): FilterQueryBuilder
    {
        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($id)) || null === ($elements = $filterConfig->getElements())) {
            return $queryBuilder;
        }

        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!\is_array($types) || empty($types)) {
            return $queryBuilder;
        }

        $filterData = $filterConfig->getData();
        $preselectionData = $this->getPreselectData($id, $preselections);

        $filterConfig->setData($preselectionData);

        /** @var FilterPreselectModel $preselection */
        foreach ($preselections as $preselection) {
            $element = $filterConfig->getElementByValue($preselection->element);

            if (!isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Filter\AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class)) {
                continue;
            }

            if (null === ($name = $type->getName($element))) {
                continue;
            }

            $type->buildQuery($queryBuilder, $element);
        }

        // restore filter data
        $filterConfig->setData($filterData);

        return $queryBuilder;
    }

    /**
     * Get preselected data based on given preselection.
     *
     * @param int                    $id            The filter id
     * @param FilterPreselectModel[] $preselections list of preselections
     */
    public function getPreselectData(int $id, array $preselections): array
    {
        $data = [];

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($id)) || null === ($elements = $filterConfig->getElements())) {
            return $data;
        }

        $types = \System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!\is_array($types) || empty($types)) {
            return $data;
        }

        // set initial filters
        foreach ($filterConfig->getElements() as $element) {
            if (!$element->isInitial || !isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Filter\AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class)) {
                continue;
            }

            if (null === ($name = $type->getName($element))) {
                continue;
            }

            $data[$name] = AbstractType::getInitialValue($element);
        }

        /** @var FilterPreselectModel $preselection */
        foreach ($preselections as $preselection) {
            $element = $filterConfig->getElementByValue($preselection->element);

            if (!isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Filter\AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class)) {
                continue;
            }

            if (null === ($name = $type->getName($element))) {
                continue;
            }

            $data[$name] = $this->getInitialValue($preselection);
        }

        return $data;
    }

    /**
     * Get the initial value based on preselection.
     *
     * @return array|mixed|null
     */
    public function getInitialValue(FilterPreselectModel $element)
    {
        $value = null;

        switch ($element->initialValueType) {
            case AbstractType::VALUE_TYPE_ARRAY:
                $value = array_map(
                    function ($val) {
                        return $val['value'];
                    },
                    StringUtil::deserialize($element->initialValueArray, true)
                );

                break;

            default:
                $value = $element->initialValue;

                break;
        }

        return $value;
    }
}
