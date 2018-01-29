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
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class DateType extends AbstractType implements TypeInterface
{
    const START_SUFFIX = 'start';
    const STOP_SUFFIX  = 'stop';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
//        $builder->whereElement($element, $this->getName($element,$element['name']), $this->config);
        $data = $this->config->getData();


//		if(null !== $data['date_start'] && null !== $data['date_stop'])
//		{return;}

        if (isset($data['date_start']) && '' != $data['date_start']) {
            $element['field'] = $element['startField'];

            $builder->whereElement($element, $this->getStartValueName($element), $this->config);
        }

        if (isset($data['date_stop']) && '' != $data['date_stop']) {
            $builder->whereElement($element, $this->getStopValueName($element), $this->config);
        }

    }

    /**
     * @param FilterConfigElementModel $element
     *
     * @return string
     */
    protected function getStartValueName(FilterConfigElementModel $element): string
    {
        return $element->name.'_'.static::START_SUFFIX;
    }

    /**
     * @param FilterConfigElementModel $element
     *
     * @return string
     */
    protected function getStopValueName(FilterConfigElementModel $element): string
    {
        return $element->name.'_'.static::STOP_SUFFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        if (isset($element->startField) && '' !== $element->startField && isset($element->endField) && '' !== $element->endField) {
            $this->buildRangeForm($element, $builder);

            return;
        }

        if (isset($element->startField) && '' !== $element->startField) {
            $this->buildStartForm($element, $builder);
        }

        if (isset($element->endField) && '' !== $element->endField) {
            $this->buildStopForm($element, $builder);
        }
    }

    /**
     * Add the start stop form field
     *
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     */
    protected function buildRangeForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $builder->create($this->getName($element, $element->name), FormType::class, ['inherit_data' => true])->add(static::START_SUFFIX, \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, $this->getOptions($element, $builder))->add(static::STOP_SUFFIX, \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, $this->getOptions($element, $builder))
        );
    }

    /**
     * Add the start form field
     *
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     */
    protected function buildStartForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $this->getStartName($element),
            \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
            $this->getOptions($element, $builder)
        );
    }

    /**
     * Add the stop form field
     *
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     */
    protected function buildStopForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $this->getStopName($element),
            \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
            $this->getOptions($element, $builder)
        );
    }

    /**
     * Get the name of the stop element
     *
     * @param FilterConfigElementModel $element
     *
     * @return string
     */
    protected function getStartName(FilterConfigElementModel $element): string
    {
        return $this->getName($element, $element->name).'_'.static::START_SUFFIX;
    }

    /**
     * Get the name of the stop element
     *
     * @param FilterConfigElementModel $element
     *
     * @return string
     */
    protected function getStopName(FilterConfigElementModel $element): string
    {
        return $this->getName($element, $element->name).'_'.static::STOP_SUFFIX;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options = $this->setFormat($element, $options);
        $options = $this->setLimits($options, $element);

        $options['widget']                    = 'single_text';
        $options['with_minutes']              = $options['with_seconds'] = false;
        $options['attr']['data-linked-start'] = '#'.$builder->getName().'_'.$this->getStartName($element);
        $options['attr']['data-linked-end']   = '#'.$builder->getName().'_'.$this->getStopName($element);

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
    protected function setLimits(FilterConfigElementModel $options, array $element): array
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

}
