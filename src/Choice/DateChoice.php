<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\Type\PublishedType;
use HeimrichHannot\FilterBundle\Filter\Type\SkipParentsType;
use HeimrichHannot\FilterBundle\Filter\Type\SqlType;
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
        $table = $filter['dataContainer'];

        /** @var FilterConfigElementModel $element */
        $element = $context['element'];

        /** @var FilterConfigElementModel[]|Collection $elements */
        $elements = \is_array($context['elements']) || $context['elements'] instanceof \Model\Collection ? $context['elements'] : [$context['elements']];

        $columns = [];
        $values = [];

        foreach ($elements as $entry) {
            switch ($entry->type) {
                case SkipParentsType::TYPE:
                    $skipParentsType = new SkipParentsType(System::getContainer()->get('huh.filter.config'));

                    list($elementColumns, $elementValues) = $skipParentsType->buildQueryForModels($filter, $entry);

                    $columns = array_merge($columns, $elementColumns);
                    $values = array_merge($values, $elementValues);

                    break;

                case PublishedType::TYPE:
                    $publishedType = new PublishedType(System::getContainer()->get('huh.filter.config'));

                    list($elementColumns, $elementValues) = $publishedType->buildQueryForModels($filter, $entry);

                    $columns = array_merge($columns, $elementColumns);
                    $values = array_merge($values, $elementValues);

                    break;

                case SqlType::TYPE:
                    $columns[] = Controller::replaceInsertTags($entry->whereSql, false);

                    break;

                default:
                    if ($entry->isInitial && $entry->id !== $element->id) {
                        switch ($entry->initialValueType) {
                            case AbstractType::VALUE_TYPE_SCALAR:
                                $operator = System::getContainer()->get('huh.utils.database')->transformVerboseOperator($entry->operator);

                                $columns[] = $table.'.'.$entry->field.' '.$operator.' ?';
                                $values[] = $entry->initialValue;

                                break;

                            case AbstractType::VALUE_TYPE_ARRAY:
                                $value = array_column(StringUtil::deserialize($entry->initialValueArray), 'value');

                                if (empty($value) || empty($value[0])) {
                                    break;
                                }

                                $columns[] = $table.'.'.$entry->field.' IN ('.implode(',', $value).')';

                                break;
                        }
                    }

                    break;
            }
        }

        $options = [];

        if (isset($context['latest']) && true === $context['latest']) {
            $options['order'] = $table.'.'.$element->field.' DESC';
            $options['limit'] = 1;
        } else {
            $options['order'] = $table.'.'.$element->field.' ASC';
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
