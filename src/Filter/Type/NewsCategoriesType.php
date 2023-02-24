<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;

class NewsCategoriesType extends ChoiceType
{
    public const TYPE = 'news_categories';

    /**
     * {@inheritDoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $value = $this->config->getData()[$this->getName($element)] ?? null;

        if (!$value) {
            return;
        }

        if (!\is_array($value)) {
            $value = [$value];
        }

        $criteria = new NewsCriteria($this->config->getFramework());
        $criteria->setCategories($value);

        foreach ($criteria->getColumns() as $column) {
            $builder->andWhere($column);
        }
    }

    public function getChoices(FilterConfigElementModel $element): array
    {
        $categories = StringUtil::deserialize($element->cf_newsCategories);
        $categories = NewsCategoryModel::getAllSubcategoriesIds($categories);

        $options = [];

        foreach ($categories as $id) {
            $model = NewsCategoryModel::findByPk($id);

            if ($model) {
                $options[$model->id] = $model->getTitle();
            }
        }

        return $options;
    }
}
