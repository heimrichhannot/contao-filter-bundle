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

class TimeType extends AbstractType
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
    protected function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);

        $options = $this->addTimeWidgetOptions($options, $element, $builder);

        $options['widget'] = $element->timeWidget ?: DateType::WIDGET_TYPE_CHOICE;

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
    protected function addTimeWidgetOptions(array $options, FilterConfigElementModel $element, FormBuilderInterface $builder): array
    {
        $time = time();
        $type = $element->timeWidget ?: DateType::WIDGET_TYPE_CHOICE;

        switch ($type) {
            case DateType::WIDGET_TYPE_SINGLE_TEXT:
                $options['html5'] = (bool) $element->html5;
                $options['format'] = $options['attr']['format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToRFC3339($element->timeFormat);

                if (true === $options['html5']) {
                    if ('' !== $element->minTime) {
                        $options['attr']['min'] = Date::parse('\TH:i', $element->minTime); // valid rfc 3339 date `\TH:i` format must be used
                    }

                    if ('' !== $element->maxTime) {
                        $options['attr']['max'] = Date::parse('\TH:i', $element->maxTime); // valid rfc 3339 date `\TH:i` format must be used
                    }

                    break;
                }

                $options['group_attr']['class'] .= ' timepicker';
                $options['attr']['data-enable-time'] = 'true';
                $options['attr']['data-no-calendar'] = 'true';

                $options['attr']['data-date-format'] = $element->timeFormat;

                if ('' !== $element->minTime) {
                    $options['attr']['data-min-date'] = Date::parse($element->dateFormat, $element->minTime);
                }

                if ('' !== $element->maxTime) {
                    $options['attr']['data-max-date'] = Date::parse($element->dateFormat, $element->maxTime);
                }

                break;
            case DateType::WIDGET_TYPE_CHOICE:
                // time restriction must be configurable by itself
                break;
        }

        return $options;
    }
}
