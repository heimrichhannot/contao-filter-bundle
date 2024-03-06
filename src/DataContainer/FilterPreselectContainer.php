<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\FilterCollection;
use HeimrichHannot\FilterBundle\Filter\Type\ChoiceType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterPreselectContainer
{
    private RequestStack     $requestStack;
    private Utils            $utils;
    private array            $bundleConfig;
    private FilterCollection $filterCollection;
    private FilterConfig     $filterConfig;

    public function __construct(RequestStack $requestStack, Utils $utils, array $bundleConfig, FilterCollection $filterCollection, FilterConfig $filterConfig)
    {
        $this->requestStack = $requestStack;
        $this->utils = $utils;
        $this->bundleConfig = $bundleConfig;
        $this->filterCollection = $filterCollection;
        $this->filterConfig = $filterConfig;
    }

    /**
     * @Callback(table="tl_filter_preselect", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'tl_content' !== $dc->parentTable || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $preselectModel = FilterPreselectModel::findByPk($dc->id);
        if (null === $preselectModel) {
            return;
        }

        $filterConfigElement = FilterConfigElementModel::findByPk($preselectModel->element);
        if (null === $filterConfigElement) {
            return;
        }

        $choices = $this->prepareElementChoices($filterConfigElement);

        $dca = &$GLOBALS['TL_DCA']['tl_filter_preselect'];

        if ($filterConfigElement->multiple) {
            $dca['fields']['initialValueType']['options'];
        }

        $dca['fields']['initialValue']['inputType'] = 'select';
        $dca['fields']['initialValue']['options'] = $choices;
        $dca['fields']['initialValue']['eval']['chosen'] = true;

        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['inputType'] = 'select';
        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['options'] = $choices;
        $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['eval']['chosen'] = true;
    }

    public function prepareElementChoices(FilterConfigElementModel $filterConfigElement): array
    {
        $filter = FilterConfigModel::findByPk($filterConfigElement->pid);

        if (!$filter) {
            return [];
        }

        $choiceType = $this->filterCollection->getFilterTypeById($filterConfigElement);

        if (!($choiceType instanceof ChoiceType)) {
            return [];
        }

        $this->filterConfig->buildForm([], [
            'overrideFilter' => $filter->row(),
            'skipSession' => true,
            'skipAjax' => true,
        ]);
        $builder = $this->filterConfig->getBuilder();

        $options = $choiceType->getOptions($filterConfigElement, $builder);
        $choices = $options['choices'] ?? [];

        if (!\is_array($choices)) {
            return [];
        }

        if (\count($choices, \COUNT_RECURSIVE) === \count($choices)) {
            $choices = array_flip($choices);
        } else {
            $choicesNew = [];

            foreach ($choices as $key => $value) {
                if (\is_array($value)) {
                    $choicesNew[$key] = array_flip($value);
                } else {
                    $choicesNew[$value] = $key;
                }
            }
            $choices = $choicesNew;
        }

        return $choices;
    }
}
