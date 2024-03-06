<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
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

class DateType extends AbstractType
{
    const TYPE = 'date';

    const WIDGET_TYPE_CHOICE = 'choice';
    const WIDGET_TYPE_TEXT = 'text';
    const WIDGET_TYPE_SINGLE_TEXT = 'single_text';

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

        if ($element->isInitial) {
            $value = $data[$name] ?? $this->getInitialValue($element, $builder->getContextualValues());

            // replace insertTags only for initial values (sql-injection protection)
            $value = System::getContainer()->get('huh.utils.date')->getTimeStamp($value, true);
        } else {
            if (!isset($data[$name]) || !$data[$name]) {
                return;
            }
            $value = $data[$name];
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
        $andXA->add($builder->expr()->lte(':start_'.$element->id, $field));
        $andXA->add($builder->expr()->gte(':stop_'.$element->id, $field));

        $builder->andWhere($andXA);

        $builder->setParameter('start_'.$element->id, $start);
        $builder->setParameter('stop_'.$element->id, $stop);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $builder->add(
            $this->getName($element),
            \Symfony\Component\Form\Extension\Core\Type\DateType::class,
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

        $options = $this->addDateWidgetOptions($options, $element, $builder);
        $options['widget'] = $element->dateWidget ?: static::WIDGET_TYPE_CHOICE;

        return $options;
    }

    /**
     * Add the options for the date_widget property.
     *
     * @throws \Exception
     */
    protected function addDateWidgetOptions(array $options, FilterConfigElementModel $element, FormBuilderInterface $builder): array
    {
        $time = time();
        $type = $element->dateWidget ?: static::WIDGET_TYPE_CHOICE;

        switch ($type) {
            case static::WIDGET_TYPE_SINGLE_TEXT:
                $element->dateFormat = $element->dateFormat ?: 'd.m.Y';
                $options['html5'] = (bool) $element->html5;
                $options['format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToRFC3339($element->dateFormat);

                if (true === $options['html5']) {
                    if ($element->minDate) {
                        $options['attr']['min'] = Date::parse('Y-m-d', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minDate)); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    if ($element->maxDate) {
                        $options['attr']['max'] = Date::parse('Y-m-d', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxDate)); // valid rfc 3339 date `YYYY-MM-DD` format must be used
                    }

                    break;
                }

                $options['group_attr']['class'] = isset($options['group_attr']['class']) ? $options['group_attr']['class'].' datepicker' : 'datepicker';
                $options['attr']['data-iso8601-format'] = System::getContainer()->get('huh.utils.date')->transformPhpDateFormatToISO8601($element->dateFormat);
                $options['attr']['data-date-format'] = $element->dateFormat;

                if ('' !== $element->minDate) {
                    $options['attr']['data-min-date'] = Date::parse($element->dateFormat, (int) strtotime(Controller::replaceInsertTags($element->minDate, false)));
                }

                if ('' !== $element->maxDate) {
                    $options['attr']['data-max-date'] = Date::parse($element->dateFormat, (int) strtotime(Controller::replaceInsertTags($element->maxDate, false)));
                }

                break;

            case static::WIDGET_TYPE_CHOICE:
                $minYear = Date::parse('Y', strtotime('-5 year', $time));
                $maxYear = Date::parse('Y', strtotime('+5 year', $time));
                $minMonth = null;

                if ($element->minDate) {
                    $minYear = Date::parse('Y', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->minDate));
                }

                if ($element->maxDate) {
                    $maxYear = Date::parse('Y', System::getContainer()->get('huh.utils.date')->getTimeStamp($element->maxDate));
                }

                $options['years'] = range($minYear, $maxYear, 1);

                // months and days restriction must be configurable by itself
                break;
        }
        $options['attr']['type'] = $type;

        return $options;
    }
}
