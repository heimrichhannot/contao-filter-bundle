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

class SkipParentsType extends AbstractType
{
    const TYPE = 'skip_parents';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (!$element->parentField) {
            return;
        }

        $filter = $this->config->getFilter();

        if (!isset($filter['dataContainer'])) {
            return;
        }

        $parentField = $filter['dataContainer'].'.'.$element->parentField;

        if (null === ($parents = System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            $filter['dataContainer'], ["$parentField != '' AND $parentField IS NOT NULL AND $parentField != 0"], []))) {
            return;
        }

        $parentIds = array_unique($parents->fetchEach($element->parentField));

        if (empty($parentIds)) {
            return;
        }

        $andNotNull = $builder->expr()->andX();
        $andNotNull->add($builder->expr()->isNotNull($parentField))->add($builder->expr()->neq($parentField, 0))->add($builder->expr()->neq($parentField, '""'));

        $notIn = $builder->expr()->notIn($filter['dataContainer'].'.id', $parentIds);

        $or = $builder->expr()->orX();
        $or->add($andNotNull);
        $or->add($notIn);

        $and = $builder->expr()->andX();
        $and->add($or);

        $builder->andWhere($and);
    }

    public static function generateModelArrays(array $filter, FilterConfigElementModel $element)
    {
        $parentField = $filter['dataContainer'].'.'.$element->parentField;

        if (null === ($parents = System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
                $filter['dataContainer'], ["$parentField != '' AND $parentField IS NOT NULL AND $parentField != 0"], []))) {
            return false;
        }

        $parentIds = array_unique($parents->fetchEach($element->parentField));

        if (empty($parentIds)) {
            return false;
        }

        $columns = [
            "$parentField IS NOT NULL AND $parentField != 0 AND $parentField != '' OR ".$filter['dataContainer'].'.id NOT IN ('.implode(',', $parentIds).')',
        ];

        $values = [];

        return ['columns' => $columns, 'values' => $values];
    }

    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_NOT_IN;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    /**
     * Check if the preview mode is enabled.
     *
     * @param bool $isIgnored
     *
     * @return bool
     */
    protected function isPreviewMode(bool $isIgnored = false)
    {
        if ($isIgnored) {
            return false;
        }

        return \defined('BE_USER_LOGGED_IN') && true === BE_USER_LOGGED_IN;
    }
}
