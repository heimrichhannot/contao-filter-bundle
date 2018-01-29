<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use function Clue\StreamFilter\remove;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

class DateRangeType extends AbstractType implements TypeInterface
{
    const START_SUFFIX = 'start';
    const STOP_SUFFIX  = 'stop';

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
    }

    /**
     * @param array $element
     *
     * @return string
     */
    protected function getStartValueName(FilterConfigElementModel $element): string
    {
        return $element['name'].'_'.static::START_SUFFIX;
    }

    /**
     * @param array $element
     *
     * @return string
     */
    protected function getStopValueName(FilterConfigElementModel $element): string
    {
        return $element['name'].'_'.static::STOP_SUFFIX;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $startElement = $this->config->getElementByValue($element['startElement']);
        $stopElement  = $this->config->getElementByValue($element['stopElement']);

        if (null === $startElement || null === $stopElement) {
            return;
        }

        $start = $builder->get($startElement['name']);
        $stop  = $builder->get($stopElement['name']);

        $group = $builder->create($this->getName($element), FormType::class, ['inherit_data' => true]);

        $group->add($start->getName(), get_class($start), $start->getOptions());
        $group->add($stop->getName(), get_class($stop), $stop->getOptions());

        $builder->remove($start->getName());
        $builder->remove($stop->getName());
    }


    /**
     * {@inheritdoc}
     */
    public function getName(FilterConfigElementModel $element, $default = null)
    {
        return parent::getName($element, $element->name);
    }


}
