<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Database;
use Contao\StringUtil;
use Contao\System;
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

        System::getContainer()->get('huh.utils.dca')->loadDc($filter['dataContainer']);

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']];

        if (!isset($data[$name])) {
            return;
        }

        $wildcard = ':'.$name;
        $fields = StringUtil::deserialize($element->fields, true);

        if (empty($fields)) {
            return;
        }

        $textualFields = [];

        $conditions = [];

        foreach ($fields as $field) {
            if ('cfgTags' === $dca['fields'][$field]['inputType']) {
                $associationTable = 'tl_cfg_tag_'.System::getContainer()->get('huh.utils.string')
                        ->removeLeadingString('tl_', $filter['dataContainer']);
                $associationProperty = System::getContainer()->get('huh.utils.string')
                        ->removeLeadingString('tl_', $filter['dataContainer']).'_id';

                $builder->innerJoin($filter['dataContainer'], $associationTable, 'ta', $filter['dataContainer'].'.id='.'ta.'.$associationProperty);
                $builder->innerJoin($filter['dataContainer'], 'tl_cfg_tag', 'tn', 'ta.cfg_tag_id='.'tn.id');

                $andWhere = $builder->expr()->andX();
                $andWhere->add('tn.name LIKE '.$wildcard);

                $conditions[] = $andWhere;
                $builder->setParameter($wildcard, '%'.strtolower($data[$name]).'%');
            } elseif (Database::getInstance()->fieldExists($field, $filter['dataContainer'])) {
                $textualFields[] = $field;
            }
        }

        if (!empty($textualFields)) {
            $concat = 'CONCAT('.implode(
                    '," ",',
                    array_map(
                        function ($field) use ($filter) {
                            return 'COALESCE(LOWER('.$filter['dataContainer'].'.'.$field.'), "")';
                        },
                        $textualFields
                    )
                ).')';

            $conditions[] = $builder->expr()->like($concat, $wildcard);

            $builder->setParameter($wildcard, '%'.strtolower($data[$name]).'%');
        }

        if (empty($conditions)) {
            return;
        }

        // combine everything in a disjunction
        $or = $builder->expr()->orX();

        foreach ($conditions as $condition) {
            $or->add($condition);
        }

        $builder->andWhere($or);
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
