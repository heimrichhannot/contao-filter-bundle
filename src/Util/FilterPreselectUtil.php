<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;

class FilterPreselectUtil
{
    protected ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
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
        $filterConfig = System::getContainer()->get('huh.filter.manager')->findById($id);
        if (null === $filterConfig
            || null === $filterConfig->getElements())
        {
            return $queryBuilder;
        }

        $types = System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!is_array($types) || empty($types)) {
            return $queryBuilder;
        }

        $filterData = $filterConfig->getData();
        $preselectionData = $this->getPreselectData($id, $preselections);

        $filterConfig->setData($preselectionData);

        /** @var FilterPreselectModel $preselection */
        foreach ($preselections as $preselection)
        {
            $element = $filterConfig->getElementByValue($preselection->element);

            if (!isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, AbstractType::class)) {
                continue;
            }

            if (null === $type->getName($element)) {
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

        $filterConfig = System::getContainer()->get('huh.filter.manager')->findById($id);
        if (null === $filterConfig
            || null === $filterConfig->getElements())
        {
            return $data;
        }

        $types = System::getContainer()->get('huh.filter.choice.type')->getCachedChoices();

        if (!is_array($types) || empty($types)) {
            return $data;
        }

        // set initial filters
        foreach ($filterConfig->getElements() as $element)
        {
            if (!$element->isInitial || !isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, AbstractType::class)) {
                continue;
            }

            $name = $type->getName($element);
            if (null === $name) {
                continue;
            }

            $data[$name] = AbstractType::getInitialValue($element);
        }

        /** @var FilterPreselectModel $preselection */
        foreach ($preselections as $preselection)
        {
            $element = $filterConfig->getElementByValue($preselection->element);

            if (!isset($types[$element->type])) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var AbstractType $type */
            $type = new $class($filterConfig);

            if (!is_subclass_of($type, AbstractType::class)) {
                continue;
            }

            $name = $type->getName($element);
            if (null === $name) {
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
    public function getInitialValue(FilterPreselectModel $element): mixed
    {
        return match ($element->initialValueType) {
            AbstractType::VALUE_TYPE_ARRAY => array_map(
                function ($val) {
                    return $val['value'];
                },
                StringUtil::deserialize($element->initialValueArray, true)
            ),
            default => $element->initialValue,
        };
    }
}
