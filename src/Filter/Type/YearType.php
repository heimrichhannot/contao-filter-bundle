<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\Form\FormBuilderInterface;

class YearType extends ChoiceType
{
    const TYPE = 'year';
    /**
     * @var DateUtil
     */
    protected $dateUtil;

    public function __construct(FilterConfig $config)
    {
        parent::__construct($config);
        $this->dateUtil = System::getContainer()->get('huh.utils.date');
    }

    public function getOptions(FilterConfigElementModel $element, FormBuilderInterface $builder)
    {
        $options = parent::getOptions($element, $builder);
        $min = $element->minDate ? $this->dateUtil->getTimeStamp($element->minDate, false) : null;
        $max = $element->maxDate ? $this->dateUtil->getTimeStamp($element->maxDate, false) : null;
        $options['choices'] = $this->getYears($min, $max);

        return $options;
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
}
