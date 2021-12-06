<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @deprecated since 1.12, use HeimrichHannot\FilterBundle\FilterType\Type\ButtonType
 */
class ButtonType extends AbstractType
{
    const TYPE = 'button';

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
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return $element->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $label = parent::getLabel($element, $builder);

        if ('' === $label && '' !== $element->label) {
            return $element->label;
        }

        return $label;
    }

    public function getHideLabel(FilterConfigElementModel $element): bool
    {
        return false;
    }
}
