<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class IntegerType extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, array $element)
    {
        $builder->whereElement($element, $this->getName($element), $this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(array $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options['grouping'] = (bool) $element['grouping'];
        $options['scale'] = (int) $element['scale'];
        $options['rounding_mode'] = (int) $element['roundingMode'];

        return $options;
    }
}
