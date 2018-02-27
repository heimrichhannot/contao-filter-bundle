<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Date;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class PublishedType extends ChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $filter = $this->config->getFilter();
        $and = $builder->expr()->andX();

        if ($element->addStartAndStop && !$this->isPreviewMode($element->ignoreFePreview)) {
            $time = Date::floorToMinute();

            $orStart = $builder->expr()->orX(
                $builder->expr()->eq($filter['dataContainer'].'.'.$element->startField, '""'),
                $builder->expr()->lte($filter['dataContainer'].'.'.$element->startField, ':startField_time')
            );
            $and->add($orStart);

            $orStop = $builder->expr()->orX(
                $builder->expr()->eq($filter['dataContainer'].'.'.$element->stopField, '""'),
                $builder->expr()->gt($filter['dataContainer'].'.'.$element->stopField, ':stopField_time')
            );
            $and->add($orStop);

            $builder->setParameter(':startField_time', $time);
            $builder->setParameter(':stopField_time', $time + 60);
        }

        $and->add($builder->expr()->eq($filter['dataContainer'].'.'.$element->field, $element->invertField ? '""' : 1));

        $builder->andWhere($and);
    }

    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
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
