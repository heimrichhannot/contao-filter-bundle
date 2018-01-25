<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class ResetType extends AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, array $element)
    {
        // TODO: Implement buildQuery() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $element, FormBuilderInterface $builder)
    {
        $filter = $this->config->getFilter();

        if (!$this->config->hasData() || true === (bool) $filter['renderEmpty']) {
            return;
        }

        $name = $this->getName($element);

        // use SubmitType instead of ResetType, because ResetType wont submit the form (client-side only)
        $builder->add($name, \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, $this->getOptions($element, $builder));
        $this->config->addResetName($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function getName(array $element, $default = null)
    {
        return parent::getName($element, 'reset');
    }
}
