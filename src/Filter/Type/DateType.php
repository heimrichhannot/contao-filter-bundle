<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Config;
use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\Form\Type\DateRangeType;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

class DateType extends AbstractType implements TypeInterface
{
	const START_SUFFIX = 'start';
	const STOP_SUFFIX  = 'stop';
	
	/**
	 * {@inheritdoc}
	 */
	public function buildQuery(FilterQueryBuilder $builder, array $element)
	{
		$data = $this->config->getData();
		$startName = $this->getStartName($element);
		$stopName = $this->getStopName($element);
		
//		if(null !== $data[$startName] && null !== $data[$stopName])
//		{return;}
		
		if(isset($data[$startName]) && '' != $data[$startName])
		{
			// set field to startField for query building
			$element['field'] = $element['startField'];
			
			$builder->whereElement($element, $startName, $this->config);
		}
		
		if(isset($data[$stopName]) && '' != $data[$stopName])
		{
			// set field to stopField for query building
			$element['field'] = $element['stopField'];
			
			$builder->whereElement($element, $stopName, $this->config);
		}
		
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $element, FormBuilderInterface $builder)
	{
		if (isset($element['startField']) && '' !== $element['startField'] && isset($element['stopField']) && '' !== $element['stopField']) {
			$this->buildRangeForm($element, $builder);
			
			return;
		}
		
		if (isset($element['startField']) && '' !== $element['startField']) {
			$this->buildStartForm($element, $builder);
		}
		
		if (isset($element['stopField']) && '' !== $element['stopField']) {
			$this->buildStopForm($element, $builder);
		}
	}
	
	/**
	 * Add the start stop form field
	 *
	 * @param array                $element
	 * @param FormBuilderInterface $builder
	 */
	protected function buildRangeForm(array $element, FormBuilderInterface $builder)
	{
		$options = [
			'start' => [
				'name'    => $this->getStartName($element),
				'class'   => \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
				'options' => $this->getOptions($element, $builder)
			]
		];
		
		$builder->add($this->getName($element, $element['name']), DateRangeType::class, $options);
	}
	
	/**
	 * Add the start form field
	 *
	 * @param array                $element
	 * @param FormBuilderInterface $builder
	 */
	protected function buildStartForm(array $element, FormBuilderInterface $builder)
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
	 * @param array                $element
	 * @param FormBuilderInterface $builder
	 */
	protected function buildStopForm(array $element, FormBuilderInterface $builder)
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
	 * @param array $element
	 *
	 * @return string
	 */
	protected function getStartName(array $element): string
	{
		return $this->getName($element, $element['name']) . '_' . static::START_SUFFIX;
	}
	
	/**
	 * Get the name of the stop element
	 *
	 * @param array $element
	 *
	 * @return string
	 */
	protected function getStopName(array $element): string
	{
		return $this->getName($element, $element['name']) . '_' . static::STOP_SUFFIX;
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	protected function getOptions(array $element, FormBuilderInterface $builder)
	{
		$options = parent::getOptions($element, $builder);
		
		$options = $this->setFormat($element, $options);
		$options = $this->setLimits($options, $element);
		
		$options['widget']                    = 'single_text';
		$options['with_minutes']              = $options['with_seconds'] = false;
		$options['attr']['data-linked-start'] = '#' . $builder->getName() . '_' . $this->getStartName($element);
		$options['attr']['data-linked-end']   = '#' . $builder->getName() . '_' . $this->getStopName($element);
		
		return $options;
	}
	
	
	/**
	 * set top and bottom limit for form field
	 *
	 * @param array $options
	 * @param array $element
	 *
	 * @return array
	 */
	protected function setLimits(array $options, array $element): array
	{
		if ($element['minDate']) {
			$options['attr']['data-min-date'] = date($element['dateFormat'], $element['minDate']);
		}
		
		if ($element['maxDate']) {
			$options['attr']['data-max-date'] = date($element['dateFormat'], $element['maxDate']);
		}
		
		if ($element['minTime']) {
			$options['attr']['data-min-time'] = $element['minTime'];
		}
		
		if ($element['maxTime']) {
			$options['attr']['data-max-time'] = $element['maxTime'];
		}
		
		return $options;
	}
	
	
	/**
	 * set time format for form field
	 *
	 * @param array $element
	 * @param array $options
	 *
	 * @return array
	 */
	protected function setFormat(array $element, array $options): array
	{
		$options['attr']['data-date-format'] = $element['dateFormat'];
		
		$options['attr']['data-moment-date-format'] = System::getContainer()->get('huh.utils.date')->formatPhpDateToJsDate($element['dateFormat']);
		
		return $options;
	}
	
}
