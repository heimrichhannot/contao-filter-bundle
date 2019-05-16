<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Contao\Controller;
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
use Symfony\Component\DependencyInjection\ContainerInterface;

class YearChoice extends AbstractChoice
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, ModelUtil $modelUtil)
    {
        parent::__construct($container->get('contao.framework'));
        $this->modelUtil = $modelUtil;
        $this->container = $container;
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
                    $skipParentsType = new SkipParentsType($this->container->get('huh.filter.config'));

                    list($elementColumns, $elementValues) = $skipParentsType->buildQueryForModels($filter, $entry);

                    $columns = array_merge($columns, $elementColumns);
                    $values = array_merge($values, $elementValues);

                    break;

                case PublishedType::TYPE:
                    $publishedType = new PublishedType($this->container->get('huh.filter.config'));

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
                                $operator = $this->container->get('huh.utils.database')->transformVerboseOperator($entry->operator);

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
        $items = $this->modelUtil->findModelInstancesBy($filter['dataContainer'], $columns, $values, $options);

        if (!$items) {
            return [];
        }
        $years = [];

        if ($element->addOptionCount)
        {
            $entryCount = [];
        }

        foreach ($items as $entry) {
            $date = date('Y', $entry->{$element->field});
            if ($element->addOptionCount) {
                $entryCount[$date] = isset($entryCount[$date]) ? ++$entryCount[$date] : 1;
            }
            $years[$date] = $date;
        }

        if ($element->addOptionCount)
        {
            foreach ($years as $value => $label)
            {
                if (!$element->optionCountLabel)
                {
                    $years[$value] = $label.' ('.$entryCount[$value].')';
                }
                else {
                    $years[$value] = $this->container->get('translator')->trans($element->optionCountLabel, [
                        '%value%' => $value,
                        '%count%' => $entryCount[$value],
                    ]);
                }
            }
            $years = array_flip($years);
        }

        krsort($years, SORT_NUMERIC);
        return $years;
    }
}
