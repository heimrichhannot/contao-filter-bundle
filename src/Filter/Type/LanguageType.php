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

class LanguageType extends ChoiceType
{
    const TYPE = 'language';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\LanguageType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(FilterConfigElementModel $element)
    {
        if (!System::getContainer()->has('huh.filter.choice.language')) {
            return [];
        }

        return System::getContainer()->get('huh.filter.choice.language')->getCachedChoices([$element, $this->config->getFilter()]);
    }
}
