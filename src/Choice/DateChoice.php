<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
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
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class DateChoice extends AbstractChoice
{
    private DatabaseUtilPolyfill $dbUtil;
    private InsertTagParser $insertTagParser;

    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack,
        Utils $utils,
        KernelInterface $kernel,
        DatabaseUtilPolyfill $dbUtil,
        InsertTagParser $insertTagParser
    ) {
        parent::__construct($framework, $requestStack, $utils, $kernel);
        $this->dbUtil = $dbUtil;
        $this->insertTagParser = $insertTagParser;
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
                    $columns[] = $this->insertTagParser->replace($entry->whereSql);

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
            $translatedDate = $this->translateMonths($date);

            $dates[$translatedDate] = $translatedDate;
        }

        krsort($dates, SORT_NUMERIC);

        return $dates;
    }

    /**
     * Translates available months inside a given string from English to the current language.
     */
    public function translateMonths(string $date): array|string
    {
        $monthsMap = array_flip($this->getMonthTranslationMap());
        foreach ($monthsMap as $translated => $english) {
            if (str_contains($date, $english)) {
                $date = str_replace($english, $translated, $date);
            }
        }

        $shortMonthsMap = array_flip($this->getMonthTranslationMap(true));
        foreach ($shortMonthsMap as $translated => $english) {
            if (str_contains($date, $english)) {
                $date = str_replace($english, $translated, $date);
            }
        }

        return $date;
    }

    public function getMonthTranslationMap(bool $short = false): array
    {
        $map = [];

        $months = $short ? [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
        ] : [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];

        System::loadLanguageFile('default');

        foreach ($GLOBALS['TL_LANG'][$short ? 'MONTHS_SHORT' : 'MONTHS'] as $index => $translated) {
            $map[$months[$index]] = $translated;
        }

        return $map;
    }
}
