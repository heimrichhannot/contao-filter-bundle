<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 26.01.18
 * Time: 10:37
 */

namespace HeimrichHannot\FilterBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeType extends FormType
{
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			[
				'start' => new DateTimeType(),
				'stop'  => new DateTimeType(),
			]
		);
	}
	
	public function getParent()
	{
		return DateTimeType::class;
	}
}