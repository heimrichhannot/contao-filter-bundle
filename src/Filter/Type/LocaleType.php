<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Form\FormBuilderInterface;

class LocaleType extends ChoiceType
{
    const TYPE = 'locale';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\LocaleType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(FilterConfigElementModel $element)
    {
        if (!System::getContainer()->has('huh.filter.choice.locale')) {
            return [];
        }

        return System::getContainer()->get('huh.filter.choice.locale')->getCachedChoices([$element, $this->config->getFilter()]);
    }
}
