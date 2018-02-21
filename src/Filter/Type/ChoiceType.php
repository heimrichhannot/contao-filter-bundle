<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $builder->whereElement($element, $this->getName($element), $this->config, $this->getDefaultOperator($element));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_EQUAL;
    }

    /**
     * Get the list of available choices.
     *
     * @param FilterConfigElementModel $element
     *
     * @return array|mixed
     */
    public function getChoices(FilterConfigElementModel $element)
    {
        return System::getContainer()->get('huh.filter.choice.field_options')->getCachedChoices(['element' => $element, 'filter' => $this->config->getFilter()]);
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);
        $options['choices'] = $this->getChoices($element);
        $options['choice_translation_domain'] = false; // disable translation]

        if (isset($options['attr']['placeholder'])) {
            $options['attr']['data-placeholder'] = $options['attr']['placeholder'];
            $options['placeholder'] = $options['attr']['placeholder'];
            unset($options['attr']['placeholder']);

            $options['required'] = false;
            $options['empty_data'] = $options['placeholder'];
        }

        $options['expanded'] = (bool) $element->expanded;
        $options['multiple'] = (bool) $element->multiple;

        // forgiving array handling
        if ($element->addDefaultValue) {
            if ($options['multiple'] && !is_array($options['data'])) {
                $options['data'] = [$options['data']];
            }
        }

        return $options;
    }
}
