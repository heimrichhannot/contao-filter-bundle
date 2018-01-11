<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;

interface TypeInterface
{
    /**
     * Build the filter query
     * @param FilterQueryBuilderInterface $builder The query builder
     * @param array $data The form data
     * @param boolean $count Distinguish between count or fetch query
     */
    public function buildQuery(FilterQueryBuilderInterface $builder, array $data = [], $count = false);

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