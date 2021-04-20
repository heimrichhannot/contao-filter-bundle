<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeCollection;
use HeimrichHannot\FilterBundle\FilterType\InitialFilterTypeInterface;
use HeimrichHannot\FilterBundle\FilterType\Type\ButtonType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
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
    protected ContainerUtil $container;

    public function __construct(array $bundleConfig, TypeChoice $typeChoice, FilterTypeCollection $typeCollection, ContainerUtil $container)
    {
        $this->bundleConfig = $bundleConfig;
        $this->typeChoice = $typeChoice;
        $this->typeCollection = $typeCollection;
        $this->container = $container;
    }

    public function onLoadCallback(DataContainer $dc): void
    {
        if ('edit' === \Input::get('act') && $this->container->isBackend()) {
            $model = FilterConfigElementModel::findByIdOrAlias($dc->id);
            $type = $this->typeCollection->getType($model->type);

            if ($type instanceof InitialFilterTypeInterface && $model->isInitial) {
                $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];
                $prependPalette = '{initial_legend},isInitial;{general_legend},title,type;';
                $appendPalette = '{publish_legend},published;';

                $dca['palettes'][$model->type] = $type->getInitialPalette($prependPalette, $appendPalette);
            }
        }
    }

    public function getTypeOptions(DataContainer $dc)
    {
        if (!$this->bundleConfig['filter']['disable_legacy_filters']) {
            return $this->typeChoice->getCachedChoices($dc);
        }

        $options = [];

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

    public function getOperators(DataContainer $dc)
    {
        if (!$this->bundleConfig['filter']['disable_legacy_filters']) {
            return DatabaseUtil::OPERATORS;
        }

        return $this->typeCollection->getType($dc->activeRecord->type)->getOperators();
    }

    public function getDateWidgetOptions(DataContainer $dc): array
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

    public function getButtonTypes(DataContainer $dc): array
    {
        return ButtonType::BUTTON_TYPES;
    }
}
