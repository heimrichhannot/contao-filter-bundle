<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\Type\PublishedType;
use HeimrichHannot\FilterBundle\Filter\Type\SkipParentsType;
use HeimrichHannot\FilterBundle\Filter\Type\SqlType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;
use HeimrichHannot\FilterBundle\Util\DatabaseUtilPolyfill;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use const SORT_NUMERIC;

class YearChoice extends AbstractChoice
{
    private InsertTagParser $insertTagParser;

    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack,
        Utils $utils,
        KernelInterface $kernel,
        InsertTagParser $insertTagParser
    ) {
        parent::__construct($framework, $requestStack, $utils, $kernel);
        $this->insertTagParser = $insertTagParser;
    }

    /**
     * @return array
     */
    protected function collect(): array
    {
        if (!is_array($this->getContext()) || empty($this->getContext())) {
            return [];
        }
        $context = $this->getContext();

        $filter = $context['filter'];
        $table = $filter['dataContainer'];

        /** @var FilterConfigElementModel $element */
        $element = $context['element'];

        /** @var FilterConfigElementModel[]|Collection $elements */
        $elements = is_array($context['elements']) || $context['elements'] instanceof Collection ? $context['elements'] : [$context['elements']];

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

                case SqlType::TYPE:
                    $columns[] = $this->insertTagParser->parse($entry->whereSql);

                    break;

                default:
                    if ($entry->isInitial && $entry->id !== $element->id) {
                        switch ($entry->initialValueType) {
                            case AbstractType::VALUE_TYPE_SCALAR:
                                $operator = DatabaseUtilPolyfill::transformVerboseOperator($entry->operator);

                                $columns[] = $table.'.'.$entry->field.' '.$operator.' ?';
                                $values[] = $entry->initialValue;

                                break;

                            case AbstractType::VALUE_TYPE_ARRAY:
                                $value = array_column(StringUtil::deserialize($entry->initialValueArray), 'value');

                                if (empty($value) || empty($value[0])) {
                                    continue 3;
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
        }

        if (empty($columns)) {
            return [];
        }
        $items = $this->utils->model()->findModelInstancesBy($filter['dataContainer'], $columns, $values, $options);

        if (!$items) {
            return [];
        }
        $years = [];

        if ($element->addOptionCount) {
            $entryCount = [];
        }

        foreach ($items as $entry) {
            $date = date('Y', $entry->{$element->field});

            if ($element->addOptionCount) {
                $entryCount[$date] = isset($entryCount[$date]) ? ++$entryCount[$date] : 1;
            }
            $years[$date] = $date;
        }

        if ($element->addOptionCount) {
            foreach ($years as $value => $label) {
                if (!$element->optionCountLabel) {
                    $years[$value] = $label.' ('.$entryCount[$value].')';
                } else {
                    $years[$value] = System::getContainer()->get('translator')
                        ->trans($element->optionCountLabel, [
                            '%value%' => $value,
                            '%count%' => $entryCount[$value],
                        ]);
                }
            }
        }

        krsort($years, SORT_NUMERIC);

        return $years;
    }
}
