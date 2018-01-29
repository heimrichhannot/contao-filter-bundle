<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

interface TypeInterface
{


    /**
     * Build the filter query.
     *
     * @param FilterQueryBuilder       $builder The query builder
     * @param FilterConfigElementModel $element The element data
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element);

    /**
     * Builds the form, add your filter fields here.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FilterConfigElementModel $element The element data
     * @param FormBuilderInterface     $builder The form builder
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder);


    /**
     * Get the field name.
     *
     * @param FilterConfigElementModel $element
     *
     * @return mixed
     */
    public function getName(FilterConfigElementModel $element);


    /**
     * Get the default form element name
     *
     * @param FilterConfigElementModel $element The element data
     *
     * @return string|null
     */
    public function getDefaultName(FilterConfigElementModel $element);
}
