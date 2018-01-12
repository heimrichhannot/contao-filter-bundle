<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

interface TypeInterface
{
    /**
     * Build the filter query
     * @param FilterQueryBuilder $builder The query builder
     * @param array $element The element data
     */
    public function buildQuery(FilterQueryBuilder $builder, array $element);

    /**
     * Builds the form, add your filter fields here
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param array $element The element data
     * @param FormBuilderInterface $builder The form builder
     */
    public function buildForm(array $element, FormBuilderInterface $builder);
}