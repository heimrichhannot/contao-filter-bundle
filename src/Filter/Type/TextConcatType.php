<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Query\QueryBuilder;
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

                $subBuilder = new QueryBuilder(System::getContainer()->get('database_connection'));

                $conditions[] = $builder->expr()->in($filter['dataContainer'].'.id',
                    $subBuilder->select('ta.'.$associationProperty)
                        ->from($associationTable, 'ta')
                        ->join('ta', 'tl_cfg_tag', 'tn', 'ta.cfg_tag_id='.'tn.id')
                        ->where('tn.name LIKE '.$wildcard)->getSQL()
                );
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
        }

        if (empty($conditions)) {
            return;
        }

        $builder->setParameter($wildcard, '%'.strtolower($data[$name]).'%');

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

    /**
     * @return array
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $options = parent::getOptions($element, $builder, $triggerEvent);
        $builderOptions = $builder->getOptions();
        $filter = $builderOptions['filter'];
        $filterConfig = $filter->getFilter();

        if ($element->submitOnInput && $filterConfig['asyncFormSubmit']) {
            $options['attr']['data-submit-on-input'] = '1';
            $options['attr']['data-threshold'] = $element->threshold;
            $options['attr']['data-debounce'] = $element->debounce;
        }

        return $options;
    }
}
