<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Choice\YearChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class YearType extends ChoiceType
{
    const TYPE = 'year';
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
        $this->modelUtil = System::getContainer()->get('huh.utils.model');
        $this->optionsChoice = System::getContainer()->get('huh.filter.choice.year');
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
            $value = $data[$name] ?? $this->getInitialValue($element, $builder->getContextualValues());
        }
        if (!$this->validYear($value)) {
            return;
        }

        $start = $this->getYearStart($value);
        $stop = $this->getYearEnd($value);

        $andXA = $builder->expr()->andX();
        $andXA->add($builder->expr()->lte(':start', $field));
        $andXA->add($builder->expr()->gte(':stop', $field));

        $builder->andWhere($andXA);

        $builder->setParameter(':start', $start);
        $builder->setParameter(':stop', $stop);
    }

    public function getChoices(FilterConfigElementModel $element)
    {
        if ($element->addParentSelector && $element->parentField) {
            $parentElement = $this->config->getElementByValue($element->parentField);
            if ($parentElement && !empty($choices = $this->optionsChoice->getCachedChoices([
                'element' => $element,
                'filter' => $this->config->getFilter(),
                'parentElement' => $parentElement,
            ]))) {
                return $choices;
            }
        }
        $min = $element->minDate ? $this->dateUtil->getTimeStamp($element->minDate, false) : null;
        $max = $element->maxDate ? $this->dateUtil->getTimeStamp($element->maxDate, false) : null;

        return $this->getYears($min, $max);
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

    public function validYear(int $year)
    {
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
}
