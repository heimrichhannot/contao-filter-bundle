<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class DateType extends AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $this->getName($element),
            \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
            $this->getOptions($element, $builder)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options = $this->setFormat($element, $options);
        $options = $this->setLimits($options, $element);

        $options['widget']       = 'single_text';
        $options['with_minutes'] = $options['with_seconds'] = false;

        return $options;
    }


    /**
     * set top and bottom limit for form field
     *
     * @param array                    $options
     * @param FilterConfigElementModel $element
     *
     * @return array
     */
    protected function setLimits(array $options, FilterConfigElementModel $element): array
    {
        if (true === (bool)$element->minDate) {
            $options['attr']['data-min-date'] = date($element->dateFormat, $element->minDate);
        }

        if (true === (bool)$element->maxDate) {
            $options['attr']['data-max-date'] = date($element->dateFormat, $element->maxDate);
        }

        if (true === (bool)$element->minTime) {
            $options['attr']['data-min-time'] = $element->minTime;
        }

        if (true === (bool)$element->maxTime) {
            $options['attr']['data-max-time'] = $element->maxTime;
        }

        return $options;
    }


    /**
     * set time format for form field
     *
     * @param FilterConfigElementModel $element
     * @param array                    $options
     *
     * @return array
     */
    protected function setFormat(FilterConfigElementModel $element, array $options): array
    {
        $options['attr']['data-date-format'] = $element->dateFormat;

        $options['attr']['data-moment-date-format'] = System::getContainer()->get('huh.utils.date')->formatPhpDateToJsDate($element->dateFormat);

        return $options;
    }


    /**
     * @inheritdoc
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return $element->name;
    }
}
