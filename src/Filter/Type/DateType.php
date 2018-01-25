<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Config;
use Contao\Controller;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
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
		if (isset($element['startField']) && '' !== $element['startField']) {
			$builder->add(
				$this->getStartName($element),
				\Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
				$this->getOptions($element, $builder)
			);
		}
		
		if (isset($element['endField']) && '' !== $element['endField']) {
			$builder->add(
				$this->getStopName($element),
				\Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
				$this->getOptions($element, $builder)
			);
		}
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
		
		$options = $this->setFormat($options, $element['dateTimeFormat']);
		
		$options['widget'] = 'single_text';
		
		if ('date' == $element['dateTimeFormat']) {
			$this->setDatepickerOptions($options, $element);
		}
		
		$options = $this->setLimits($options, $element);
		
		$options['attr']['data-linked-start'] = '#Veranstaltungen_startDate';
		$options['attr']['data-linked-start'] = '#Veranstaltungen_endDate';
		
		
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
	 * @param $options array
	 * @param $rgxp    string
	 */
	protected function setFormat($options, $rgxp)
	{
		$format = $this->getDateTimeFormat($rgxp);
		
		$options['format']                          = $format;
		$options['attr']['data-date-format']        = $format;
		$options['attr']['data-moment-date-format'] = DateUtil::formatPhpDateToJsDate($format);
		
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
