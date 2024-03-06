<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Model\ModelUtil;
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

        $parents = System::getContainer()->get(ModelUtil::class)->findModelInstancesBy(
            $filter['dataContainer'], ["$parentField != '' AND $parentField IS NOT NULL AND $parentField != 0"], []
        );
        if (null === $parents) {
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

    public function buildQueryForModels(array $filter, FilterConfigElementModel $element)
    {
        $columns = [];
        $values = [];

        $parentField = $filter['dataContainer'].'.'.$element->parentField;

        $parents = System::getContainer()->get(ModelUtil::class)->findModelInstancesBy(
            $filter['dataContainer'], ["$parentField != '' AND $parentField IS NOT NULL AND $parentField != 0"], []
        );
        if (null === $parents) {
            return [$columns, $values];
        }

        $parentIds = array_unique($parents->fetchEach($element->parentField));

        if (empty($parentIds)) {
            return [$columns, $values];
        }

        $columns = [
            "($parentField IS NOT NULL AND $parentField != 0 AND $parentField != '' OR ".$filter['dataContainer'].'.id NOT IN ('.implode(',', $parentIds).'))',
        ];

        return [$columns, $values];
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
