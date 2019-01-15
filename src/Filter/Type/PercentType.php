<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class PercentType extends TextType
{
    const TYPE = 'percent';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\PercentType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_EQUAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options['scale'] = (int) $element->scale;
        $options['type'] = (string) $element->percentType ?: 'fractional';

        return $options;
    }
}
