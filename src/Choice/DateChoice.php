<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\Type\SkipParentsType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\AbstractChoice;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class DateChoice extends AbstractChoice
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
        if (!\is_array($this->getContext()) || empty($this->getContext())) {
            return [];
        }
        $context = $this->getContext();

        $filter = $context['filter'];
        /** @var FilterConfigElementModel $element */
        $element = $context['element'];
        /** @var FilterConfigElementModel[]|Collection $elements */
        $elements = $context['elements'];
        $columns = [];
        $values = [];

        foreach ($elements as $entry) {
            switch ($entry->type) {
                case SkipParentsType::TYPE:
                    $condition = SkipParentsType::generateModelArrays($filter, $entry);

                    if (\is_array($condition)) {
                        $columns = array_merge($columns, $condition['columns']);
                        $values = array_merge($values, $condition['values']);
                    }

                    break;

                default:
                    if ($entry->isInitial && $entry->id !== $element->id) {
                        switch ($entry->initialValueType) {
                            case AbstractType::VALUE_TYPE_SCALAR:
                                $operator = System::getContainer()->get('huh.utils.database')->transformVerboseOperator($entry->operator);

                                $columns[] = $entry->field.$operator.'?';
                                $values[] = $entry->initialValue;

                                break;

                            case AbstractType::VALUE_TYPE_ARRAY:
                                $value = array_column(StringUtil::deserialize($entry->initialValueArray), 'value');

                                if (empty($value) || empty($value[0])) {
                                    continue;
                                }
                                $columns[] = $entry->field.' IN ('.implode(',', $value).')';

                                break;
                        }
                    }

                    break;
            }
        }
        $options = [];

        if (isset($context['latest']) && true === $context['latest']) {
            $options['order'] = $element->field.' DESC';
            $options['limit'] = 1;
        } else {
            $options['order'] = $element->field.' ASC';
        }

        if (empty($columns)) {
            return [];
        }

        $items = $this->modelUtil->findModelInstancesBy($filter['dataContainer'], $columns, $values, $options);

        if (!$items) {
            return [];
        }
        $dates = [];

        foreach ($items as $entry) {
            $date = date($element->dateFormat, $entry->{$element->field});
            $translatedDate = System::getContainer()->get('huh.utils.date')->translateMonths($date);

            $dates[$translatedDate] = $translatedDate;
        }
        krsort($dates, SORT_NUMERIC);

        return $dates;
    }
}
