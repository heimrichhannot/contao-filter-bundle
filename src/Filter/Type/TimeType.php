<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\Date;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @deprecated since 1.12 and will be removed in version 2.0
 */
class TimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $data = $this->config->getData();
        $filter = $this->config->getFilter();
        $name = $this->getName($element);

        Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            return;
        }

        $field = $filter['dataContainer'].'.'.$element->field;
        $value = isset($data[$name]) && $data[$name] ? $data[$name] : 0;

        if ($element->isInitial) {
            $value = $data[$name] ?? $this->getInitialValue($element, $builder->getContextualValues());

            // replace insertTags only for initial values (sql-injection protection)
            $value = System::getContainer()->get('huh.utils.date')->getTimeStamp($value, true);
        }

        /** @var \DateTime|null $startDate */
        $value = System::getContainer()->get('huh.utils.date')->getTimeStamp($value, false);

        $minDate = $this->getMinDate($element);
        $maxDate = $this->getMaxDate($element);

        $start = $value;
        $stop = $value;

        $start = $start < $minDate ? $minDate : $start;
        $start = $start > $maxDate ? $maxDate : $start;

        $stop = $stop < $minDate ? $minDate : $stop;
        $stop = $stop > $maxDate ? $maxDate : $stop;

        $andXA = $builder->expr()->andX();
        $andXA->add($builder->expr()->lte(':start', $field));
        $andXA->add($builder->expr()->gte(':stop', $field));

        $builder->andWhere($andXA);

        $builder->setParameter(':start', $start);
        $builder->setParameter(':stop', $stop);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $this->getName($element),
            \Symfony\Component\Form\Extension\Core\Type\TimeType::class,
            $this->getOptions($element, $builder)
        );
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
        return DatabaseUtil::OPERATOR_EQUAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $options = parent::getOptions($element, $builder, $triggerEvent);

        $options = $this->addTimeWidgetOptions($options, $element, $builder);

        $options['widget'] = $element->timeWidget ?: DateType::WIDGET_TYPE_CHOICE;

        return $options;
    }

    /**
     * Add the options for the date_widget property.
     *
     * @throws \Exception
     */
    public function addTimeWidgetOptions(array $options, FilterConfigElementModel $element, FormBuilderInterface $builder): array
    {
        $time = time();
        $type = $element->timeWidget ?: DateType::WIDGET_TYPE_CHOICE;

        switch ($type) {
            case DateType::WIDGET_TYPE_SINGLE_TEXT:
                $element->timeFormat = $element->timeFormat ?: 'H:i';
                $options['html5'] = (bool) $element->html5;
                $options['attr']['format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToRFC3339($element->timeFormat);

                if (true === $options['html5']) {
                    if ($element->minTime) {
                        $options['attr']['min'] = Date::parse('\TH:i', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minTime)); // valid rfc 3339 date `\TH:i` format must be used
                    }

                    if ($element->maxTime) {
                        $options['attr']['max'] = Date::parse('\TH:i', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxTime)); // valid rfc 3339 date `\TH:i` format must be used
                    }

                    break;
                }

                $options['group_attr']['class'] = 'timepicker';
                $options['attr']['data-iso8601-format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToISO8601($element->timeFormat);
                $options['attr']['data-enable-time'] = 'true';
                $options['attr']['data-no-calendar'] = 'true';

                $options['attr']['data-date-format'] = $element->timeFormat;

                if ($element->minTime) {
                    $options['attr']['data-min-date'] = Date::parse($element->timeFormat, System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minTime));
                }

                if ($element->maxTime) {
                    $options['attr']['data-max-date'] = Date::parse($element->timeFormat, System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxTime));
                }

                break;

            case DateType::WIDGET_TYPE_CHOICE:
                // time restriction must be configurable by itself
                break;
        }

        return $options;
    }
}
