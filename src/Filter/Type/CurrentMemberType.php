<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class CurrentMemberType extends AbstractType
{
    const TYPE = 'current_member';

    const TYPE_USERNAME = 'username';
    const TYPE_ID = 'id';

    const TYPES = [
        self::TYPE_USERNAME,
        self::TYPE_ID,
    ];

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (null === ($user = $GLOBALS['TL_USERNAME']) && $element->type !== static::TYPE) {
            return;
        }

        $filter = $this->config->getFilter();
        $where = $this->getWhere($builder, $element, $filter);
        $builder->andWhere($where);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return System::getContainer()->get('huh.utils.database')::OPERATOR_EQUAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }

    protected function getOperator($element)
    {
        if (!$element->customOperator) {
            return $this->getDefaultOperator($element);
        }

        return $element->operator;
    }

    protected function getWhere(FilterQueryBuilder $builder, FilterConfigElementModel $element, ?array $filter)
    {
        $value = '';

        switch ($element->currentUserAssign) {
            case self::TYPE_ID:
                if (null === ($member = System::getContainer()->get('huh.utils.model')->findOneModelInstanceBy('tl_member', ['tl_member.username=?'], [$GLOBALS['TL_USERNAME']]))) {
                    break;
                }
                $value = $member->id;

                break;

            case self::TYPE_USERNAME:
                $value = $GLOBALS['TL_USERNAME'];

                break;

            default:
                break;
        }

        $field = $filter['dataContainer'].'.'.$element->field;

        $operator = $this->getOperator($element);
        $dca = $this->getDca($filter, $element);

        return System::getContainer()->get('huh.utils.database')->composeWhereForQueryBuilder($builder, $field, $operator, $dca, $value);
    }

    protected function getDca(array $filter, FilterConfigElementModel $element): array
    {
        return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field];
    }
}
