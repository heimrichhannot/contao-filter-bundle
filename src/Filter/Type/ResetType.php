<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class ResetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $filter = $this->config->getFilter();

        if (false === (bool) $element->alwaysShow && !$this->config->hasData() || (isset($filter['renderEmpty']) && true === (bool) $filter['renderEmpty'])) {
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
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return 'reset';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
    }
}
