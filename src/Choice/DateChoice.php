<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\Type\PublishedType;
use HeimrichHannot\FilterBundle\Filter\Type\SkipParentsType;
use HeimrichHannot\FilterBundle\Filter\Type\SqlType;
use HeimrichHannot\FilterBundle\Filter\Type\YearType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;
use HeimrichHannot\FilterBundle\Util\DatabaseUtilPolyfill;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class DateChoice extends AbstractChoice
{
    private Utils $utils;
    private DatabaseUtilPolyfill $dbUtil;

    public function __construct(
        ContaoFramework $framework,
        Utils $utils,
        DatabaseUtilPolyfill $dbUtil
    ) {
        parent::__construct($framework);
        $this->utils = $utils;
        $this->dbUtil = $dbUtil;
    }

    /**
     * @return array
     */
    protected function collect(): array
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
        $elements = \is_array($context['elements']) || $context['elements'] instanceof Collection ? $context['elements'] : [$context['elements']];

        $columns = [];
        $values = [];

        foreach ($elements as $entry) {
            switch ($entry->type) {
                case SkipParentsType::TYPE:
                    $skipParentsType = new SkipParentsType(System::getContainer()->get('huh.filter.config'));

                    [$elementColumns, $elementValues] = $skipParentsType->buildQueryForModels($filter, $entry);

                    $columns = array_merge($columns, $elementColumns);
                    $values = array_merge($values, $elementValues);

                    break;

                case PublishedType::TYPE:
                    $publishedType = new PublishedType(System::getContainer()->get('huh.filter.config'));

                    [$elementColumns, $elementValues] = $publishedType->buildQueryForModels($filter, $entry);

                    $columns = array_merge($columns, $elementColumns);
                    $values = array_merge($values, $elementValues);

                    break;

                case YearType::TYPE:
                    $yearType = new YearType(System::getContainer()->get('huh.filter.config'));

                    [$elementColumns, $elementValues] = $yearType->buildQueryForModels($filter, $entry);

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
                                $operator = $this->dbUtil->transformVerboseOperator($entry->operator);

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

        $items = $this->utils->model()->findModelInstancesBy($filter['dataContainer'], $columns, $values, $options);

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
