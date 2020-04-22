<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormButtonExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ButtonType::class;
    }

    /**
     * Add the extra row_attr option.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'group_attr' => null,
            ]
        );
    }

    /**
     * Pass the set row_attr options to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['group_attr'] = isset($options['group_attr']) && \is_array($options['group_attr']) ? $options['group_attr'] : [];
    }
}
