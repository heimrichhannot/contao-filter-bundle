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
//    	$element['field'] = $element['startField'];
//        $builder->whereElement($element, $this->getName($element), $this->config);
//
//		$element['field'] = $element['endField'];
//		$builder->whereElement($element, $this->getName($element), $this->config);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $element, FormBuilderInterface $builder)
	{
		if (isset($element['startField']) && '' !== $element['startField'] && isset($element['endField']) && '' !== $element['endField']) {
			$this->buildRangeForm($element, $builder);
			
			return;
		}
		
		if (isset($element['startField']) && '' !== $element['startField']) {
			$this->buildStartForm($element, $builder);
		}
		
		if (isset($element['endField']) && '' !== $element['endField']) {
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
		
		$options['widget'] = 'single_text';
		
		if ('date' == $element['dateTimeFormat']) {
			$this->setDatepickerOptions($options, $element);
		}
		
		$options = $this->setLimits($options, $element);
		
		$formName = $builder->getName();
		
		$options['attr']['data-linked-start'] = '#' . $formName . '_' . $this->getStartName($element);
		$options['attr']['data-linked-end']   = '#' . $formName . '_' . $this->getStopName($element);
		
		
		return $options;
	}
	
	
	protected function setDatepickerOptions(&$options, $element)
	{
		$options['with_minutes'] = $options['with_seconds'] = false;
		
	}
	
	
	protected function setLimits($options, $element)
	{
		if ($element['minDate']) {
			$options['attr']['data-min-date'] = Controller::replaceInsertTags($element['minDate']);
		}
		
		if ($element['maxDate']) {
			$options['attr']['data-max-date'] = Controller::replaceInsertTags($element['maxDate']);
		}
		
		if ($element['minTime']) {
			$options['attr']['data-min-time'] = Controller::replaceInsertTags($element['minTime']);
		}
		
		if ($element['maxTime']) {
			$options['attr']['data-max-time'] = Controller::replaceInsertTags($element['maxTime']);
		}
		
		return $options;
	}
	
	
	/**
	 * @param array $options
	 * @param array $rgxp
	 *
	 * @return array
	 */
	protected function setFormat(array $element, array $options): array
	{
//		$format = $this->getDateTimeFormat($rgxp);
		
		$options['format']                   = $element['dateFormat'];
		$options['attr']['data-date-format'] = $element['dateFormat'];
		
		$options['attr']['data-moment-date-format'] = System::getContainer()->get('huh.utils.date')->formatPhpDateToJsDate($element['dateFormat']);
		
		return $options;
	}
	
	/**
	 * @param $element array
	 *
	 * @return string
	 */
	protected function getDateTimeFormat($format)
	{
		$dateTimeFormat = '';
		
		switch ($format) {
			case 'datim':
				$dateTimeFormat = Config::get('datimFormat');
				break;
			case 'date':
				$dateTimeFormat = Config::get('dateFormat');
				break;
			case 'time':
				$dateTimeFormat = Config::get('timeFormat');
				break;
		}
		
		return $dateTimeFormat;
	}
}
