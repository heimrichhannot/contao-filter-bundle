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
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeCollection;
use HeimrichHannot\FilterBundle\FilterType\InitialFilterTypeInterface;
use HeimrichHannot\FilterBundle\FilterType\PlaceholderFilterTypeInterface;
use HeimrichHannot\FilterBundle\FilterType\Type\ButtonType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\MessageChoice;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

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

    public function __construct(
        array $bundleConfig,
        TypeChoice $typeChoice,
        FilterTypeCollection $typeCollection,
        ContainerUtil $container,
        MessageChoice $messageChoice
    ) {
        $this->bundleConfig = $bundleConfig;
        $this->typeChoice = $typeChoice;
        $this->typeCollection = $typeCollection;
        $this->container = $container;
        $this->messageChoice = $messageChoice;
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

    public function onOperatorOptionsCallback(DataContainer $dc)
    {
        if (!$this->bundleConfig['filter']['disable_legacy_filters']) {
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
        if ($this->bundleConfig['filter']['disable_legacy_filers']) {
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
