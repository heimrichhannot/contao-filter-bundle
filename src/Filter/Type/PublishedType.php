<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Date;
use Contao\Input;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class PublishedType extends AbstractType
{
    const TYPE = 'visible';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        if (!$element->field) {
            return;
        }

        if ($this->isPreviewMode((bool) $element->ignoreFePreview)) {
            return;
        }

        $filter = $this->config->getFilter();
        $and = $builder->expr()->andX();

        if ($element->addStartAndStop) {
            $time = Date::floorToMinute();

            if ($element->startField) {
                $orStart = $builder->expr()->orX(
                    $builder->expr()->eq($filter['dataContainer'].'.'.$element->startField, '""'),
                    $builder->expr()->lte($filter['dataContainer'].'.'.$element->startField, ':startField_time')
                );

                $and->add($orStart);
                $builder->setParameter('startField_time', $time);
            }

            if ($element->stopField) {
                $orStop = $builder->expr()->orX(
                    $builder->expr()->eq($filter['dataContainer'].'.'.$element->stopField, '""'),
                    $builder->expr()->gt($filter['dataContainer'].'.'.$element->stopField, ':stopField_time')
                );
                $and->add($orStop);
                $builder->setParameter('stopField_time', $time + 60);
            }
        }

        $and->add($builder->expr()->eq($filter['dataContainer'].'.'.$element->field, $element->invertField ? '""' : 1));

        $builder->andWhere($and);
    }

    public function buildQueryForModels(array $filter, FilterConfigElementModel $element)
    {
        $columns = [];
        $values = [];

        if (!$element->field) {
            return [$columns, $values];
        }

        if ($element->addStartAndStop && !$this->isPreviewMode((bool) $element->ignoreFePreview)) {
            $time = Date::floorToMinute();

            if ($element->startField) {
                $columns[] = '('.$filter['dataContainer'].'.'.$element->startField.'="" OR '.$filter['dataContainer'].'.'.$element->startField.'<=?)';
                $values[] = $time;
            }

            if ($element->stopField) {
                $columns[] = '('.$filter['dataContainer'].'.'.$element->stopField.'="" OR '.$filter['dataContainer'].'.'.$element->stopField.'>?)';
                $values[] = $time + 60;
            }
        }

        $columns[] = $filter['dataContainer'].'.'.$element->field.'='.($element->invertField ? '""' : 1);

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
        return DatabaseUtil::OPERATOR_LIKE;
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

        if (version_compare(VERSION, '4.9', '<')) {
            $isPreview = \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true && Input::cookie('FE_PREVIEW');
        } else {
            $isPreview = \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN === true;
        }

        return $isPreview;
    }
}
