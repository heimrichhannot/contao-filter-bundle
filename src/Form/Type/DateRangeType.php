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
use Symfony\Component\Form\FormBuilderInterface;

class DateRangeType extends FormType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if (null !== $options['start']) {
			$builder->add('start', $options['start']['class'], $options['start']['options']);
		}

//		if (null !== $options['stop'] && $options['stop'] instanceof TextType) {
//			$builder->add('stop', $options['stop']);
//		}
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			[
				'start' => null,
				'stop'  => null,
			]
		);
	}
	
	public function getParent()
	{
		return DateTimeType::class;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix()
	{
		return 'date_range';
	}
}