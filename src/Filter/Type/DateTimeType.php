<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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

class DateTimeType extends AbstractType
{
    const TYPE = 'date_time';

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
            \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,
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
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options = $this->addDateWidgetOptions($options, $element, $builder);
        $options['widget'] = $element->dateWidget ?: DateType::WIDGET_TYPE_CHOICE;

        return $options;
    }

    /**
     * Add the options for the date_widget property.
     *
     * @param array                    $options
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function addDateWidgetOptions(array $options, FilterConfigElementModel $element, FormBuilderInterface $builder): array
    {
        $time = time();
        $type = $element->dateWidget ?: DateType::WIDGET_TYPE_CHOICE;

        switch ($type) {
            case DateType::WIDGET_TYPE_SINGLE_TEXT:
                $element->dateTimeFormat = $element->dateTimeFormat ?: 'd.m.Y H:i';
                $options['html5'] = (bool) $element->html5;
                $options['format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToRFC3339($element->dateTimeFormat);

                if (true === $options['html5']) {
                    if ($element->minDateTime) {
                        $options['attr']['min'] = Date::parse('Y-m-d\TH:i', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minDateTime)); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    if ($element->maxDateTime) {
                        $options['attr']['max'] = Date::parse('Y-m-d\TH:i', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxDateTime)); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    break;
                }
                $options['group_attr']['class'] = 'datepicker timepicker';
                $options['attr']['data-iso8601-format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToISO8601($element->dateTimeFormat);
                $options['attr']['data-enable-time'] = 'true';
                $options['attr']['data-date-format'] = $element->dateTimeFormat;

                if ($element->minDateTime) {
                    $options['attr']['data-min-date'] = Date::parse($element->dateTimeFormat, System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minDateTime));
                }

                if ($element->maxDateTime) {
                    $options['attr']['data-max-date'] = Date::parse($element->dateTimeFormat, System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxDateTime));
                }

                break;
            case DateType::WIDGET_TYPE_CHOICE:
                $minYear = Date::parse('Y', strtotime('-5 year', $time));
                $maxYear = Date::parse('Y', strtotime('+5 year', $time));
                $minMonth = null;

                if ($element->minDateTime) {
                    $minYear = Date::parse('Y', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minDateTime));
                }

                if ($element->maxDateTime) {
                    $maxYear = Date::parse('Y', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxDateTime));
                }

                $options['years'] = range($minYear, $maxYear, 1);

                // months and days restriction cant be configured from min and max date
                break;
        }

        return $options;
    }
}
