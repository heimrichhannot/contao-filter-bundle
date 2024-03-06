<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle;
use Codefog\NewsCategoriesBundle\Criteria\NewsCriteria;
use Codefog\NewsCategoriesBundle\Exception\NoNewsException;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;

class NewsCategoriesType extends ChoiceType
{
    public const TYPE = 'news_categories';

    public const PALETTE = '{general_legend},title,type,isInitial;{config_legend},cf_newsCategories,cf_newsCategoriesChilds,sortOptionValues,customName,expanded,multiple,submitOnChange;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;';

    public const PALETTE_INITIAL = '{config_legend},cf_newsCategories;';

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

        try {
            $criteria->setCategories($value);
        } catch (NoNewsException $e) {
            $builder->andWhere('1=0');

            return;
        }

        foreach ($criteria->getColumns() as $column) {
            $builder->andWhere($column);
        }
    }

    public function getChoices(FilterConfigElementModel $element): array
    {
        $categories = StringUtil::deserialize($element->cf_newsCategories);

        if ($element->cf_newsCategoriesChilds) {
            $update = [];

            foreach ($categories as $parentCategory) {
                $childCategories = NewsCategoryModel::getAllSubcategoriesIds($categories);

                if (\count($childCategories) > 1) {
                    unset($childCategories[array_search($parentCategory, $childCategories)]);
                    $update = array_merge($update, $childCategories);
                } else {
                    $update[] = $parentCategory;
                }
            }
            $categories = $update;
        }

        $options = [];

        foreach ($categories as $id) {
            $model = NewsCategoryModel::findByPk($id);

            if ($model) {
                $options[$model->id] = $model->getTitle();
            }
        }

        if ($element->sortOptionValues) {
            asort($options);
        }

        return $options;
    }

    public static function getInitialPalette(string $prepend, string $append): ?string
    {
        return $prepend.static::PALETTE_INITIAL.$append;
    }

    public static function isEnabledForCurrentContext(array $context = []): bool
    {
        if (isset($context['table']) && 'tl_news' !== $context['table']) {
            return false;
        }

        if (!class_exists(CodefogNewsCategoriesBundle::class)) {
            return false;
        }

        return true;
    }
}
