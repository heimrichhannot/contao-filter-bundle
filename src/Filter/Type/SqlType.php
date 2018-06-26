<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\System;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class SqlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (!$element->whereSql) {
            return;
        }

        $where = Controller::replaceInsertTags($element->whereSql, false);

        try {
            $testBuilder = clone $builder;
            $testBuilder->select('*');
            $testBuilder->where($where);
            $testBuilder->setMaxResults(1);
            $testBuilder->execute();
        } catch (SyntaxErrorException $e) {
            // force error if in frontend and debug mode
            if (System::getContainer()->getParameter('kernel.debug') && System::getContainer()->get('huh.utils.container')->isFrontend()) {
                $builder->andWhere($where);
            }

            return;
        }

        $builder->andWhere($where);
    }

    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_LIKE;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return null;
    }
}
