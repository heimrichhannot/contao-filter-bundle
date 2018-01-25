<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class TextConcatType extends AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, array $element)
    {
        $data = $this->config->getData();
        $name = $this->getName($element, $element['name']);
        $value = $data[$name];

        if (null === $value) {
            return;
        }

        $fields = StringUtil::deserialize($element['fields'], true);
        $concat = 'CONCAT('.implode('," ",', $fields).')';

        $builder->andWhere($builder->expr()->like($concat, $builder->expr()->literal('%'.$value.'%')));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\TextType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    protected function getName(array $element, $default = null)
    {
        return parent::getName($element, $element['name']);
    }
}
