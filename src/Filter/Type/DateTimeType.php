<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Date;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\FormBuilderInterface;

class DateTimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
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
        return DatabaseUtil::OPERATOR_LIKE;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options = $this->addDateWidgetOptions($options, $element, $builder);
        $options['widget'] = $element->dateWidget ?: static::WIDGET_TYPE_CHOICE;

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
                $options['html5'] = (bool) $element->html5;
                $options['format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToRFC3339($element->dateTimeFormat);

                if (true === $options['html5']) {
                    if ('' !== $element->minDate) {
                        $options['attr']['min'] = Date::parse('Y-m-d\TH:i', $element->minDate); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    if ('' !== $element->maxDate) {
                        $options['attr']['max'] = Date::parse('Y-m-d\TH:i', $element->maxDate); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    break;
                }
                $options['group_attr']['class'] .= ' datepicker timepicker';
                $options['attr']['data-enable-time'] = 'true';
                $options['attr']['data-date-format'] = $element->dateTimeFormat;

                if ('' !== $element->minDate) {
                    $options['attr']['data-min-date'] = Date::parse($element->dateTimeFormat, $element->minDate);
                }

                if ('' !== $element->maxDate) {
                    $options['attr']['data-max-date'] = Date::parse($element->dateTimeFormat, $element->maxDate);
                }

                break;
            case DateType::WIDGET_TYPE_CHOICE:
                $minYear = Date::parse('Y', strtotime('-5 year', $time));
                $maxYear = Date::parse('Y', strtotime('+5 year', $time));
                $minMonth = null;

                if ('' !== $element->minDate) {
                    $minYear = Date::parse('Y', $element->minDate);
                }

                if ('' !== $element->maxDate) {
                    $maxYear = Date::parse('Y', $element->maxDate);
                }

                $options['years'] = range($minYear, $maxYear, 1);

                // months and days restriction must be configurable by itself
                break;
        }

        return $options;
    }
}
