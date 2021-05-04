<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Type\AbstractFilterType;
use HeimrichHannot\FilterBundle\Type\Concrete\ButtonType;
use HeimrichHannot\FilterBundle\Type\FilterTypeCollection;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use HeimrichHannot\FilterBundle\Type\InitialFilterTypeInterface;
use HeimrichHannot\FilterBundle\Type\PlaceholderFilterTypeInterface;
use HeimrichHannot\UtilsBundle\Choice\MessageChoice;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class FilterConfigElementContainer
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TypeChoice
     */
    protected $typeChoice;
    /**
     * @var FilterTypeCollection
     */
    protected $typeCollection;
    /**
     * @var ContainerUtil
     */
    protected $container;

    /**
     * @var MessageChoice
     */
    protected $messageChoice;

    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    /**
     * @var FieldOptionsChoice
     */
    protected $fieldOptionsChoice;

    public function __construct(
        array $bundleConfig,
        TypeChoice $typeChoice,
        FilterTypeCollection $typeCollection,
        FieldOptionsChoice $fieldOptionsChoice,
        ContainerUtil $container,
        ModelUtil $modelUtil,
        MessageChoice $messageChoice
    ) {
        $this->bundleConfig = $bundleConfig;
        $this->typeChoice = $typeChoice;
        $this->typeCollection = $typeCollection;
        $this->container = $container;
        $this->messageChoice = $messageChoice;
        $this->modelUtil = $modelUtil;
        $this->fieldOptionsChoice = $fieldOptionsChoice;
    }

    public function onLoadCallback(DataContainer $dc): void
    {
        if ('edit' === \Input::get('act') && $this->container->isBackend()) {
            $model = FilterConfigElementModel::findByIdOrAlias($dc->id);
            $type = $this->typeCollection->getType($model->type);

            if ($type instanceof InitialFilterTypeInterface && $model->isInitial) {
                $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];
                $prependPalette = '{initial_legend},isInitial,isInitialOverridable;{general_legend},title,type;';
                $appendPalette = '{publish_legend},published;';

                $dca['palettes'][$model->type] = $type->getInitialPalette($prependPalette, $appendPalette);
            }
        }
    }

    public function onTypeOptionsCallback(DataContainer $dc): array
    {
        $options = $this->typeChoice->getCachedChoices($dc);

        foreach ($this->typeCollection->getTypes() as $key => $type) {
            $group = $type->getGroup();

            if ($dc->activeRecord->isInitial && $type instanceof InitialFilterTypeInterface) {
                $options[$group][] = $key;
            } elseif (!$dc->activeRecord->isInitial) {
                $options[$group][] = $key;
            }
        }

        // separate deprecated types
        $options['deprecated'] = [];

        foreach ($this->bundleConfig['filter']['deprecated_types'] as $deprecated) {
            foreach ($options as $key => $option) {
                if (\in_array($deprecated, $option)) {
                    $helperKey = array_search($deprecated, $option);
                    unset($options[$key][$helperKey]);
                    $options['deprecated'][] = $deprecated;
                }
            }
        }

        foreach ($options as $key => $option) {
            $options[$key] = array_values($options[$key]);
        }

        return $options;
    }

    public function onInitialValueTypeCallback(DC_Table $dc): array
    {
        $choices = AbstractFilterType::VALUE_TYPES;
        $activeRecord = $dc->activeRecord->fetchAllAssoc()[0];

        if (empty($activeRecord)) {
            return $choices;
        }

        $types = System::getContainer()->getParameter('huh.filter')['filter']['types'];
        $typeIndex = array_search($activeRecord['type'], array_column($types, 'name'), true);

        if (!$typeIndex && $this->typeCollection->getType($activeRecord['type']) instanceof InitialFilterTypeInterface) {
            return $this->typeCollection->getType($activeRecord['type'])->getInitialValueTypes($choices);
        }

        $class = $types[$typeIndex]['class'];

        return $class::VALUE_TYPES;
    }

    public function onInitialValueCallback(DC_Table $dc)
    {
        /** @var FilterConfigElementModel $element */
        if (null === ($element = $this->modelUtil->findModelInstanceByPk($dc->table, $dc->id))) {
            return null;
        }

        if ($this->typeCollection->getType($element->type) instanceof InitialFilterTypeInterface) {
            $context = new FilterTypeContext();
            $context->setElementConfig($element);
            $context->setFilterConfig($element->getRelated('pid'));

            if (!method_exists($this->typeCollection->getType($element->type), 'getInitialValueChoices')) {
                return null;
            }

            return $this->typeCollection->getType($element->type)->getInitialValueChoices($context);
        }

        return $this->fieldOptionsChoice->getCachedChoices([
                'element' => $element,
                'filter' => $element->getRelated('pid')->row(),
            ]);
    }

    public function onOperatorOptionsCallback(DataContainer $dc)
    {
        if (null === $this->typeCollection->getType($dc->activeRecord->type)) {
            return DatabaseUtil::OPERATORS;
        }

        return $this->typeCollection->getType($dc->activeRecord->type)->getOperators();
    }

    public function onPlaceholderOptionsCallback(DataContainer $dc): array
    {
        $placeholders = $this->messageChoice->getCachedChoices('huh.filter.placeholder');

        if ($dc->activeRecord->type instanceof PlaceholderFilterTypeInterface) {
            return array_merge($placeholders, $this->typeCollection->getType($dc->activeRecord->type)->getPlaceholders());
        }

        return $placeholders;
    }

    public function onDateWidgetOptionsCallback(DataContainer $dc): array
    {
        if (null === $this->typeCollection->getType($dc->activeRecord->type)) {
            return [
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_CHOICE,
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_TEXT,
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_SINGLE_TEXT,
            ];
        }

        return [
            \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_CHOICE,
            \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_SINGLE_TEXT,
        ];
    }

    public function onButtonTypeOptionsCallback(DataContainer $dc): array
    {
        return ButtonType::BUTTON_TYPES;
    }

    public function onInputGroupAppendOptionsCallback(DataContainer $dc): array
    {
        return $this->messageChoice->getCachedChoices('huh.filter.input_group_text');
    }

    public function onInputGroupPrependOptionsCallback(DataContainer $dc): array
    {
        return $this->messageChoice->getCachedChoices('huh.filter.input_group_text');
    }
}
