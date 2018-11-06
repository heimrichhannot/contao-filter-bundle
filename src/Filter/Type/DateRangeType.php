<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class DateRangeType extends AbstractType
{
    const TYPE = 'date_range';

    /**
     * @var FilterConfigElementModel
     */
    protected $startElement;

    /**
     * @var FilterConfigElementModel
     */
    protected $stopElement;

    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $data = $this->config->getData();
        $filter = $this->config->getFilter();
        $name = $this->getName($element);

        Controller::loadDataContainer($filter['dataContainer']);

        $this->startElement = $this->config->getElementByValue($element->startElement);
        $this->stopElement = $this->config->getElementByValue($element->stopElement);

        if (null === $this->startElement || null === $this->stopElement) {
            return;
        }

        if (!$this->startElement->field && !$this->stopElement->field) {
            return;
        }

        $builder->addSkip($this->startElement);
        $builder->addSkip($this->stopElement);

        $startField = $filter['dataContainer'].'.'.$this->startElement->field;
        $stopField = $filter['dataContainer'].'.'.$this->stopElement->field;

        $startName = $this->startElement->getFormName($this->config);
        $stopName = $this->stopElement->getFormName($this->config);

        /** @var \DateTime|null $startDate */
        $startDate = $startDate = $data[$name][$startName] ?? 0;

        if ($this->startElement->isInitial) {
            $startDate = $data[$name][$startName] ?? $this->getInitialValue($this->startElement, $builder->getContextualValues());

            // replace insertTags only for initial values (sql-injection protection)
            $startDate = System::getContainer()->get('huh.utils.date')->getTimeStamp($startDate, true);
        }

        /** @var \DateTime|null $stopDate */
        $stopDate = $data[$name][$stopName] ?? 9999999999999;

        if ($this->stopElement->isInitial) {
            $stopDate = $data[$name][$stopName] ?? $this->getInitialValue($this->stopElement, $builder->getContextualValues());
            // replace insertTags only for initial values (sql-injection protection)
            $stopDate = System::getContainer()->get('huh.utils.date')->getTimeStamp($stopDate, true);
        }

        $start = System::getContainer()->get('huh.utils.date')->getTimeStamp($startDate, false) ?? 0;
        $stop = System::getContainer()->get('huh.utils.date')->getTimeStamp($stopDate, false) ?? 9999999999999;

        $startFieldMinDate = $this->getMinDate($this->startElement);
        $startFieldMaxDate = $this->getMaxDate($this->startElement);
        $stopFieldMinDate = $this->getMinDate($this->stopElement);
        $stopFieldMaxDate = $this->getMaxDate($this->stopElement);

        $start = $start < $startFieldMinDate ? $startFieldMinDate : $start;
        $start = $start > $startFieldMaxDate ? $startFieldMaxDate : $start;

        $stop = $stop < $stopFieldMinDate ? $stopFieldMinDate : $stop;
        $stop = $stop > $stopFieldMaxDate ? $stopFieldMaxDate : $stop;

        if ($startField !== $stopField) {
            $or = $builder->expr()->orX();

            $andXA = $builder->expr()->andX();
            $andXA->add($builder->expr()->gte(':start', $startField));
            $andXA->add($builder->expr()->lte(':start', $stopField));

            $andXB = $builder->expr()->andX();
            $andXB->add($builder->expr()->gte(':stop', $startField));
            $andXB->add($builder->expr()->lte(':stop', $stopField));

            $andXC = $builder->expr()->andX();
            $andXC->add($builder->expr()->lte(':start', $startField));
            $andXC->add($builder->expr()->gte(':stop', $stopField));

            $builder->setParameter(':start', $start);
            $builder->setParameter(':stop', $stop);

            $or->add($andXA);
            $or->add($andXB);
            $or->add($andXC);

            $builder->andWhere($or);
        } else {
            $andXA = $builder->expr()->andX();
            $andXA->add($builder->expr()->lte(':start', $startField));
            $andXA->add($builder->expr()->gte(':stop', $stopField));

            $builder->andWhere($andXA);

            $builder->setParameter(':start', $start);
            $builder->setParameter(':stop', $stop);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $name = $this->getName($element);

        // date_range is a wrapper form, group should already exist
        if (null === $name || !$builder->has($name)) {
            return;
        }

        $this->startElement = $this->config->getElementByValue($element->startElement);
        $this->stopElement = $this->config->getElementByValue($element->stopElement);

        if (null === $this->getStartElement() || null === $this->getStopElement()) {
            null === $this->getStartElement() ? null : $builder->remove($this->getStartElement()->getFormName($this->config));
            null === $this->getStopElement() ? null : $builder->remove($this->getStopElement()->getFormName($this->config));
            $builder->remove($name);

            return;
        }

        if (!$builder->getForm()->has($this->getStartElement()->getFormName($this->config)) || !$builder->getForm()->has($this->getStopElement()->getFormName($this->config))) {
            $builder->getForm()->has($this->getStartElement()->getFormName($this->config)) ? $builder->remove($this->getStartElement()->getFormName($this->config)) : null;
            $builder->getForm()->has($this->getStopElement()->getFormName($this->config)) ? $builder->remove($this->getStopElement()->getFormName($this->config)) : null;
            $builder->remove($name);

            return;
        }

        $start = $builder->get($this->getStartElement()->getFormName($this->config));
        $stop = $builder->get($this->getStopElement()->getFormName($this->config));

        $group = $builder->get($this->getName($element));

        $group->add($this->startElement->getFormName($this->config), \get_class($start->getType()->getInnerType()),
            $this->getStartOptions($element, $builder, $start, $stop));
        $group->add($this->stopElement->getFormName($this->config), \get_class($stop->getType()->getInnerType()),
            $this->getStopOptions($element, $builder, $start, $stop));

        $group->get($this->startElement->getFormName($this->config))->setData($start->getData());
        $group->get($this->stopElement->getFormName($this->config))->setData($stop->getData());

        $builder->remove($start->getName());
        $builder->remove($stop->getName());

        $builder->add($group);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultName(FilterConfigElementModel $element)
    {
        return $element->name;
    }

    /**
     * @return FilterConfigElementModel|null
     */
    public function getStartElement(): ?FilterConfigElementModel
    {
        return $this->startElement;
    }

    /**
     * @return FilterConfigElementModel|null
     */
    public function getStopElement(): ?FilterConfigElementModel
    {
        return $this->stopElement;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOperator(FilterConfigElementModel $element)
    {
        return DatabaseUtil::OPERATOR_LIKE;
    }

    /**
     * Get the options for the start field.
     *
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     * @param FormBuilderInterface     $start
     * @param FormBuilderInterface     $stop
     *
     * @return array
     */
    protected function getStartOptions(
        FilterConfigElementModel $element,
        FormBuilderInterface $builder,
        FormBuilderInterface $start,
        FormBuilderInterface $stop
    ) {
        $options = $start->getOptions();
        $options['attr']['data-linked-end'] = sprintf('#%s_%s_%s', $builder->getName(), $this->getName($element),
            $stop->getName());

        return $options;
    }

    /**
     * Get the options for the stop field.
     *
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     * @param FormBuilderInterface     $start
     * @param FormBuilderInterface     $stop
     *
     * @return array
     */
    protected function getStopOptions(
        FilterConfigElementModel $element,
        FormBuilderInterface $builder,
        FormBuilderInterface $start,
        FormBuilderInterface $stop
    ) {
        $options = $stop->getOptions();
        $options['attr']['data-linked-start'] = sprintf('#%s_%s_%s', $builder->getName(), $this->getName($element),
            $start->getName());

        return $options;
    }
}
