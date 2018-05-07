<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Sort;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;

class BasicSort extends AbstractSort
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element, AbstractType $type, array $sortConfig)
    {
        $data = $this->config->getData();
        $value = $data[$type->getName($element)] ?: null;

        if ($this->getName($element, $type, $sortConfig) === $value) {
            $builder->addOrderBy($sortConfig['field'], $sortConfig['direction']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldText(FilterConfigElementModel $element, AbstractType $type, array $sortConfig): ?string
    {
        $field = '';

        if (!isset($sortConfig['fieldText'])) {
            return null;
        }

        $filter = $this->config->getFilter();

        Controller::loadDataContainer($filter['dataContainer']);
        Controller::loadLanguageFile($filter['dataContainer']);

        if (isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$sortConfig['field']]['label'])) {
            $field = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$sortConfig['field']]['label'][0];
        }

        return System::getContainer()->get('translator')->trans($sortConfig['fieldText'], ['%field%' => $field]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(FilterConfigElementModel $element, AbstractType $type, array $sortConfig): ?string
    {
        return $sortConfig['field'].$sortConfig['direction'];
    }
}
