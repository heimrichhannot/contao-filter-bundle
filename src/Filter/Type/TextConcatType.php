<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class TextConcatType extends AbstractType
{
    const TYPE = 'text_concat';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $data = $this->config->getData();
        $filter = $this->config->getFilter();
        $name = $this->getName($element);

        if (!isset($data[$name])) {
            return;
        }

        $wildcard = ':'.$name;
        $fields = StringUtil::deserialize($element->fields, true);

        if (empty($fields)) {
            return;
        }

        $concat = 'CONCAT('.implode(
                '," ",',
                array_map(
                    function ($field) use ($filter) {
                        return 'COALESCE(LOWER('.$filter['dataContainer'].'.'.$field.'), "")';
                    },
                    $fields
                )
            ).')';

        $builder->andWhere($builder->expr()->like($concat, $wildcard));
        $builder->setParameter($wildcard, '%'.strtolower($data[$name]).'%');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add($this->getName($element), \Symfony\Component\Form\Extension\Core\Type\TextType::class, $this->getOptions($element, $builder));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return $element->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_LIKE;
    }
}
