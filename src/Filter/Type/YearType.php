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
use HeimrichHannot\FilterBundle\Choice\YearChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Form\FormBuilderInterface;

class YearType extends ChoiceType
{
    const TYPE = 'year';

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
     * @var YearChoice
     */
    protected $optionsChoice;

    public function __construct(FilterConfig $config)
    {
        parent::__construct($config);
        $this->dateUtil = System::getContainer()->get('huh.utils.date');
        $this->modelUtil = System::getContainer()->get(ModelUtil::class);
        $this->optionsChoice = System::getContainer()->get('huh.filter.choice.year');
    }

    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
        $filter = $this->config->getFilter();
        $name = $this->getName($element);

        Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element->field])) {
            return;
        }

        $contextualValues = $builder->getContextualValues();
        $field = $filter['dataContainer'].'.'.$element->field;

        $value = $this->getValue($name, $element, $contextualValues);

        if (!$this->validYear($value)) {
            return;
        }

        $start = $this->getYearStart($value);
        $stop = $this->getYearEnd($value);

        $andXA = $builder->expr()->andX();
        $andXA->add($builder->expr()->lte(':start', $field));
        $andXA->add($builder->expr()->gte(':stop', $field));

        $builder->andWhere($andXA);

        $builder->setParameter('start', $start);
        $builder->setParameter('stop', $stop);
    }

    public function buildQueryForModels(array $filter, FilterConfigElementModel $element): array
    {
        $columns = [];
        $values = [];

        $name = $this->getName($element);
        $field = $filter['dataContainer'].'.'.$element->field;
        $value = $this->getValue($name, $element, []);

        $start = $this->getYearStart($value);
        $stop = $this->getYearEnd($value);

        $columns = [$field.'>?', $field.'<?'];
        $values = [$start, $stop];

        return [$columns, $values];
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
        $cssClasses = 'year' === $this->getName($element) ? $this->getName($element) : ' year '.$this->getName($element);

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
                $choiceOptions['min'] = Date::parse('Y', $min);
            }

            if ($max) {
                $choiceOptions['max'] = Date::parse('Y', $max);
            }

            if (!empty($choices = $this->optionsChoice->getCachedChoices($choiceOptions))) {
                return $choices;
            }
        }

        $years = $this->getYears($min, $max);

        if ($element->sortOptionValuesInverted) {
            $years = array_combine(array_reverse($years), array_reverse($years));
        }

        return $years;
    }

    /**
     * @param string $min Timestamp, default: 01.01.2000
     * @param string $max Timestamp, default: current
     *
     * @return array
     */
    public function getYears(string $min = null, string $max = null)
    {
        $min = empty($min) ? '946684800' : $min;
        $max = empty($max) ? time() : $max;
        $years = range(date('Y', $min), date('Y', $max));

        return array_combine($years, $years);
    }

    public function validYear($year)
    {
        if (!is_numeric($year)) {
            return false;
        }

        if (checkdate(1, 1, $year) && checkdate(12, 31, $year)) {
            return true;
        }

        return false;
    }

    public function getYearStart(int $year)
    {
        return mktime(0, 0, 0, 1, 1, $year);
    }

    public function getYearEnd(int $year)
    {
        return mktime(23, 59, 59, 12, 31, $year);
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
            $value = Date::parse('Y');
        }

        return $value;
    }

    /**
     * @param $name
     *
     * @return array|int|mixed|string|string[]|null
     */
    private function getValue($name, FilterConfigElementModel $element, array $contextualValues)
    {
        $data = $this->config->getData();
        $value = isset($data[$name]) && $data[$name] ? $data[$name] : 0;

        if ($element->isInitial) {
            if (static::VALUE_TYPE_LATEST === $element->initialValueType) {
                $value = $this->getLatestValue($element);
            } else {
                $value = $data[$name] ?? $this->getInitialValue($element, $contextualValues);
            }
        }

        return $value;
    }
}
