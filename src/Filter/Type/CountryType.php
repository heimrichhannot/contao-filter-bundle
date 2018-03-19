<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Form\FormBuilderInterface;

class CountryType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\CountryType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(FilterConfigElementModel $element)
    {
        if (!System::getContainer()->has('huh.filter.choice.country')) {
            return [];
        }

        return System::getContainer()->get('huh.filter.choice.country')->getCachedChoices([$element, $this->config->getFilter()]);
    }
}
