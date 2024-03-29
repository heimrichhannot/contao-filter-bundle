<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class CheckboxType extends AbstractType
{
    const TYPE = 'checkbox';

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
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, $this->getOptions($element, $builder));
        $builder->get($this->getName($element))->addModelTransformer(new class() implements DataTransformerInterface {
            public function transform($value)
            {
                return (bool) $value;
            }

            public function reverseTransform($value)
            {
                return (int) $value;
            }
        });
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
     * {@inheritdoc}
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $options = parent::getOptions($element, $builder, $triggerEvent);

        if (true === (bool) $element->customValue) {
            $options['value'] = $element->value;
        }

        if ($this->config->getFilter()['asyncFormSubmit']) {
            $options['attr']['data-submit-on-change'] = 1;
        } else {
            $options['attr']['onchange'] = 'this.form.submit()';
        }

        return $options;
    }

    public static function normalizeValue($value)
    {
        return (bool) $value ? '1' : '';
    }
}
