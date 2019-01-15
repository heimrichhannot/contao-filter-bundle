<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class SortType extends ChoiceType
{
    const TYPE = 'sort';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $types = System::getContainer()->get('huh.filter.choice.sort')->getCachedChoices();

        foreach (StringUtil::deserialize($element->sortOptions, true) as $option) {
            if (!isset($types[$option['class']])) {
                continue;
            }

            $config = $types[$option['class']];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Sort\AbstractSort $type */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Sort\AbstractSort::class)) {
                continue;
            }

            $type->buildQuery($builder, $element, $this, $option);
        }
    }

    /**
     * Get the list of available choices.
     *
     * @param FilterConfigElementModel $element
     *
     * @return array|mixed
     */
    public function getChoices(FilterConfigElementModel $element)
    {
        $options = [];

        if (!System::getContainer()->has('huh.filter.choice.sort')) {
            return [];
        }

        $types = System::getContainer()->get('huh.filter.choice.sort')->getCachedChoices();

        foreach (StringUtil::deserialize($element->sortOptions, true) as $option) {
            if (!isset($types[$option['class']])) {
                continue;
            }

            $config = $types[$option['class']];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Sort\AbstractSort $type */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Sort\AbstractSort::class)) {
                continue;
            }

            if (empty($name = $type->getName($element, $this, $option)) || empty($fieldText = $type->getFieldText($element, $this, $option))) {
                continue;
            }

            $options[$fieldText] = $name;
        }

        return $options;
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);
        $types = System::getContainer()->get('huh.filter.choice.sort')->getCachedChoices();
        $data = $this->config->getData();

        foreach (StringUtil::deserialize($element->sortOptions, true) as $sortOption) {
            if (!$sortOption['standard']) {
                continue;
            }

            if (!isset($types[$sortOption['class']])) {
                continue;
            }

            $config = $types[$sortOption['class']];
            $class = $config['class'];

            if (!class_exists($class)) {
                continue;
            }

            /** @var \HeimrichHannot\FilterBundle\Sort\AbstractSort $type */
            $type = new $class($this->config);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Sort\AbstractSort::class)) {
                continue;
            }

            if (empty($name = $type->getName($element, $this, $sortOption))) {
                continue;
            }

            if (empty($data[$this->getName($element)]) || $data[$this->getName($element)] === $name) {
                $options['data'] = $name;
            }
        }

        return $options;
    }
}
