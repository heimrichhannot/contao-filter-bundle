<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Choice\FilterChoices;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Form\FormBuilderInterface;

class CountryType extends ChoiceType
{
    const TYPE = 'country';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder): void
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\CountryType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(FilterConfigElementModel $element): array
    {
        return FilterChoices::getCountryOptions($element, $this->config->getFilter());
    }
}