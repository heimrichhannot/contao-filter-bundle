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
    const PICKER_TYPE_TIME      = 'time';
    const PICKER_TYPE_DATE      = 'date';
    const PICKER_TYPE_DATE_TIME = 'date_time';

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

        $options['attr']['data-date-format']        = $element->dateFormat;
        $options['attr']['data-moment-date-format'] = System::getContainer()->get('huh.utils.date')->formatPhpDateToJsDate($element->dateFormat);

        $options = $this->addLimits($options, $element);
        $options = $this->setPickerType($options, $element);

        $options['widget']       = 'single_text';
        $options['with_minutes'] = $options['with_seconds'] = false;

        return $options;
    }

    /**
     * Set the picker type options
     *
     * @param array                    $options
     * @param FilterConfigElementModel $element
     *
     * @return array
     */
    public function setPickerType(array $options, FilterConfigElementModel $element): array
    {
        switch ($element->datePickerType) {
            case static::PICKER_TYPE_DATE:
                $options['group_attr']['class'] .= ' datepicker';
                break;
            case static::PICKER_TYPE_TIME:
                $options['group_attr']['class']      .= ' timepicker';
                $options['attr']['data-enable-time'] = 'true';
                $options['attr']['data-no-calendar'] = 'true';
                break;
            case static::PICKER_TYPE_DATE_TIME:
                $options['group_attr']['class']      .= ' datepicker';
                $options['group_attr']['class']      .= ' timepicker';
                $options['attr']['data-enable-time'] = 'true';
                break;
        }

        return $options;
    }

    /**
     * Set the min/max time and date limits
     *
     * @param array                    $options
     * @param FilterConfigElementModel $element
     *
     * @return array $options
     */
    public function addLimits(array $options, FilterConfigElementModel $element): array
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
     * @inheritdoc
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return $element->name;
    }
}
