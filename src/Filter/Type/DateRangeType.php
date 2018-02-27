<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class DateRangeType extends AbstractType
{
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

        \Controller::loadDataContainer($filter['dataContainer']);

        $this->startElement = $this->config->getElementByValue($element->startElement);
        $this->stopElement = $this->config->getElementByValue($element->stopElement);

        if (null === $this->startElement || null === $this->stopElement) {
            return;
        }

        $startField = $filter['dataContainer'].'.'.$this->startElement->field;
        $stopField = $filter['dataContainer'].'.'.$this->stopElement->field;

        if (!$startField && !$stopField) {
            return;
        }

        $startName = $this->startElement->getFormName();
        $stopName = $this->stopElement->getFormName();

        /** @var $startDate \DateTime|null */
        $startDate = isset($data[$name][$startName]) && $data[$name][$startName] ? $data[$name][$startName] : 0;

        /** @var $stopDate \DateTime|null */
        $stopDate = isset($data[$name][$stopName]) && $data[$name][$stopName] ? $data[$name][$stopName] : null;

        $start = 0;
        $stop = 9999999999999;

        if ($startDate instanceof \DateTime) {
            $start = $startDate->getTimestamp();
        }

        if ($stopDate instanceof \DateTime) {
            $stop = $stopDate->getTimestamp();
        }

        if (null === $startDate && null === $stopDate) {
            return;
        }

        $minField = null;
        $maxField = null;

        switch ($this->startElement->type) {
            case 'time':
                $minField = 'minTime';
                $maxField = 'maxTime';
                break;
            case 'date':
                $minField = 'minDate';
                $maxField = 'maxDate';
                break;
            case 'dateTime':
                $minField = 'minDateTime';
                $maxField = 'maxDateTime';
                break;
        }

        $startFieldMinDate = (int) strtotime(Controller::replaceInsertTags($this->startElement->{$minField}, false));
        $startFieldMaxDate = '' === $this->startElement->{$maxField} ? 9999999999999 : (int) strtotime(Controller::replaceInsertTags($this->startElement->{$maxField},
            false));
        $stopFieldMinDate = (int) strtotime(Controller::replaceInsertTags($this->stopElement->{$minField}, false));
        $stopFieldMaxDate = '' === $this->stopElement->{$maxField} ? 9999999999999 : (int) strtotime(Controller::replaceInsertTags($this->stopElement->{$maxField},
            false));

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

            dump($builder->getSQL());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        // date_range is a wrapper form, group should already exist
        if (!$builder->has($this->getName($element))) {
            return;
        }

        $this->startElement = $this->config->getElementByValue($element->startElement);
        $this->stopElement = $this->config->getElementByValue($element->stopElement);

        if (null === $this->startElement || null === $this->stopElement) {
            return;
        }

        if (!$builder->has($this->startElement->getFormName()) || !$builder->has($this->stopElement->getFormName())) {
            return;
        }

        $start = $builder->get($this->startElement->getFormName());
        $stop = $builder->get($this->stopElement->getFormName());

        $group = $builder->get($this->getName($element));

        $group->add($this->startElement->getFormName(), get_class($start->getType()->getInnerType()),
            $this->getStartOptions($element, $builder, $start, $stop));
        $group->add($this->stopElement->getFormName(), get_class($stop->getType()->getInnerType()),
            $this->getStopOptions($element, $builder, $start, $stop));

        $group->get($this->startElement->getFormName())->setData($start->getData());
        $group->get($this->stopElement->getFormName())->setData($stop->getData());

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
     * @return FilterConfigElementModel
     */
    public function getStartElement(): FilterConfigElementModel
    {
        return $this->startElement;
    }

    /**
     * @return FilterConfigElementModel
     */
    public function getStopElement(): FilterConfigElementModel
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
