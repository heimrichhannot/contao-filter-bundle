<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Sort;

use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractSort
{
    /**
     * @var FilterConfig
     */
    protected $config;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(FilterConfig $config)
    {
        $this->config = $config;
        $this->translator = System::getContainer()->get('translator');
    }

    /**
     * Build the filter query.
     *
     * @param FilterQueryBuilder       $builder    The query builder
     * @param FilterConfigElementModel $element    The element data
     * @param AbstractType             $type       The filter type
     * @param array                    $sortConfig The sort config data
     */
    abstract public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element, AbstractType $type, array $sortConfig);

    /**
     * Get the option label.
     *
     * @param AbstractType $type The filter type
     */
    abstract public function getFieldText(FilterConfigElementModel $element, AbstractType $type, array $sortConfig): ?string;

    /**
     * Get the option label.
     *
     * @param AbstractType $type The filter type
     */
    abstract public function getName(FilterConfigElementModel $element, AbstractType $type, array $sortConfig): ?string;
}
