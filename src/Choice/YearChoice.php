<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class YearChoice extends AbstractChoice
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(ContaoFrameworkInterface $framework, ModelUtil $modelUtil)
    {
        parent::__construct($framework);
        $this->modelUtil = $modelUtil;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        if (!is_array($this->getContext()) || empty($this->getContext())) {
            return [];
        }
        $context = $this->getContext();

        $filter = $context['filter'];
        /** @var FilterConfigElementModel $element */
        $element = $context['element'];
        /** @var FilterConfigElementModel $parentElement */
        $parentElement = $context['parentElement'];
        $options = [];
        if (isset($context['latest']) && true === $context['latest']) {
            $options['order'] = $element->field.' DESC';
            $options['limit'] = 1;
        }

        if ($parentElement->isInitial) {
            $value = StringUtil::deserialize($parentElement->initialValueArray);
            if ($value) {
                $value = array_column($value, 'value');
            } else {
                return [];
            }
        } else {
            $value = [$parentElement->value];
        }
        if (empty($value) || empty($value[0])) {
            return [];
        }
        $items = $this->modelUtil->findModelInstancesBy($filter['dataContainer'], $parentElement->field, $value, $options);
        if (!$items) {
            return [];
        }
        $years = [];
        foreach ($items as $item) {
            $date = date('Y', $item->date);
            $years[$date] = $date;
        }
        krsort($years, SORT_NUMERIC);

        return $years;
    }
}