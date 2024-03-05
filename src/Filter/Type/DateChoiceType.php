<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\Date;
use Contao\System;
use HeimrichHannot\FilterBundle\Choice\DateChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Form\FormBuilderInterface;

class DateChoiceType extends ChoiceType
{
    const TYPE = 'date_choice';

    const VALUE_TYPES = [
        self::VALUE_TYPE_SCALAR,
        self::VALUE_TYPE_CONTEXTUAL,
        self::VALUE_TYPE_LATEST,
    ];

    /**
     * @var DateUtil
     */
    protected $dateUtil;
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var DateChoice
     */
    protected $optionsChoice;

    public function __construct(FilterConfig $config)
    {
        parent::__construct($config);
        $this->dateUtil = System::getContainer()->get('huh.utils.date');
        $this->modelUtil = System::getContainer()->get(ModelUtil::class);
        $this->optionsChoice = System::getContainer()->get('huh.filter.choice.date');
    }

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
            if (static::VALUE_TYPE_LATEST === $element->initialValueType) {
                $value = $this->getLatestValue($element);
            } else {
                $value = $data[$name] ?? $this->getInitialValue($element, $builder->getContextualValues());
            }
        }

        if (!$value) {
            return;
        }

        $value = System::getContainer()->get('huh.utils.date')->translateMonthsToEnglish($value);

        if (!$this->validDate($value, $element->dateFormat)) {
            return;
        }

        $start = $this->getDateStart($value, $element->dateFormat);
        $stop = $this->getDateEnd($value, $element->dateFormat);

        $andXA = $builder->expr()->andX();
        $andXA->add($builder->expr()->lte(':start', $field));
        $andXA->add($builder->expr()->gte(':stop', $field));

        $builder->andWhere($andXA);

        $builder->setParameter('start', $start);
        $builder->setParameter('stop', $stop);
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder, bool $triggerEvent = true)
    {
        $options = parent::getOptions($element, $builder, $triggerEvent);
        $data = $this->config->getData();
        $name = $this->getName($element);

        if ($element->addDefaultValue && !isset($data[$name])) {
            if (static::VALUE_TYPE_LATEST === $element->defaultValueType) {
                $options['data'] = $this->getLatestValue($element);
            }
        }
        $cssClasses = 'date' === $this->getName($element) ? $this->getName($element) : ' date '.$this->getName($element);

        if (isset($options['attr']['class'])) {
            $options['attr']['class'] .= $cssClasses;
        } else {
            $options['attr']['class'] = $cssClasses;
        }

        return $options;
    }

    public function getChoices(FilterConfigElementModel $element)
    {
        $min = $element->minDate ? $this->dateUtil->getTimeStamp($element->minDate, false) : null;
        $max = $element->maxDate ? $this->dateUtil->getTimeStamp($element->maxDate, false) : null;

        if ($element->dynamicOptions) {
            $choiceOptions = [
                'element' => $element,
                'elements' => $this->config->getElements(),
                'filter' => $this->config->getFilter(),
            ];

            if ($min) {
                $choiceOptions['min'] = Date::parse($element->dateFormat, $min);
            }

            if ($max) {
                $choiceOptions['max'] = Date::parse($element->dateFormat, $max);
            }

            if (!empty($choices = $this->optionsChoice->getCachedChoices($choiceOptions))) {
                return $choices;
            }
        }

        return $this->getDates($element->dateFormat, $min, $max);
    }

    /**
     * @param string $min Timestamp, default: 01.01.2000
     * @param string $max Timestamp, default: current
     *
     * @return array
     */
    public function getDates(string $dateFormat, string $min = null, string $max = null)
    {
        $period = new \DatePeriod(
            (new \DateTime())->setTimestamp($min ?? 946684800),
            new \DateInterval('P1D'),
            (new \DateTime())->setTimestamp($max ?? time())
        );

        $dates = [];

        foreach ($period as $key => $value) {
            $dates[] = $value->format($dateFormat);
        }

        return array_combine($dates, $dates);
    }

    public function validDate(string $date)
    {
        $date = date_parse($date);

        if (0 == $date['error_count'] && checkdate($date['month'], $date['day'], $date['year'])) {
            return true;
        }

        return false;
    }

    public function getDateStart(string $date, string $dateFormat)
    {
        $dateUtil = System::getContainer()->get('huh.utils.date');

        $dateTime = \DateTime::createFromFormat($dateFormat, $date);

        return mktime(0, 0, 0,
            $dateUtil->isMonthInDateFormat($dateFormat) ? $dateTime->format('m') : 1,
            $dateUtil->isDayInDateFormat($dateFormat) ? $dateTime->format('d') : 1,
            $dateUtil->isYearInDateFormat($dateFormat) ? $dateTime->format('Y') : date('Y')
        );
    }

    public function getDateEnd(string $date, string $dateFormat)
    {
        $dateUtil = System::getContainer()->get('huh.utils.date');

        $dateTime = \DateTime::createFromFormat($dateFormat, $date);

        $month = $dateUtil->isMonthInDateFormat($dateFormat) ? $dateTime->format('m') : 12;
        $year = $dateUtil->isYearInDateFormat($dateFormat) ? $dateTime->format('Y') : date('Y');

        if ($dateUtil->isDayInDateFormat($dateFormat)) {
            $day = $dateTime->format('d');
        } else {
            // compute last day in month
            $date = new \DateTime('last day of '.$year.'-'.$month);

            $day = $date->format('d');
        }

        return mktime(23, 59, 59, $month, $day, $year);
    }

    /**
     * @return mixed|string
     */
    protected function getLatestValue(FilterConfigElementModel $element)
    {
        $choiceOptions = [
            'element' => $element,
            'elements' => $this->config->getElements(),
            'filter' => $this->config->getFilter(),
            'latest' => true,
        ];

        if (!empty($choices = $this->optionsChoice->getCachedChoices($choiceOptions))) {
            $value = array_pop($choices);
        } else {
            $value = Date::parse($element->dateFormat);
        }

        return $value;
    }
}
