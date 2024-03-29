<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTypeExtension extends AbstractTypeExtension
{
    /**
     * Add the extra row_attr option.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'group_attr' => null,
                'input_group_prepend' => false,
                'input_group_append' => false,
            ]
        );
    }

    /**
     * Pass the set row_attr options to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['group_attr'] = isset($options['group_attr']) && \is_array($options['group_attr']) ? $options['group_attr'] : [];
        $view->vars['input_prepend'] = $options['input_group_prepend'];
        $view->vars['input_append'] = $options['input_group_append'];
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
