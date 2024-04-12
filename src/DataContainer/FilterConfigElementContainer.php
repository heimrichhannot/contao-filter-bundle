<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Image;
use Contao\Message;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\FilterCollection;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class FilterConfigElementContainer
{
    public const PALETTE_PREFIX = '{general_legend},title,type,isInitial;';
    public const PALETTE_SUFFIX = '{publish_legend},published;';

    private Utils $utils;
    private FilterCollection $filterCollection;
    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    private FilterManager $filterManager;
    private FilterConfig $filterConfig;

    public function __construct(Utils $utils, FilterCollection $filterCollection, TranslatorInterface $translator, RequestStack $requestStack, FilterManager $filterManager, FilterConfig $filterConfig)
    {
        $this->utils = $utils;
        $this->filterCollection = $filterCollection;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->filterManager = $filterManager;
        $this->filterConfig = $filterConfig;
    }

    /**
     * @Callback(table="tl_filter_config_element", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $element = FilterConfigElementModel::findById($dc->id);

        if (!$element) {
            return;
        }

        /** @var class-string<AbstractType> $class */
        $class = $this->filterCollection->getClassByType($element->type);

        if ($class && !$class::isEnabledForCurrentContext([
                'table' => $dc->table,
                'filterConfigElementModel' => $element,
            ])) {
            Message::addError($this->translator->trans('huh.filter.warning.not_supported_in_current_context'));
        }

        if ($this->isDublicateName($element)) {
            Message::addError($this->translator->trans('tl_filter_config_element.warningDuplicateName', [], 'contao_tl_filter_config_element'));
        }
    }

    /**
     * @Callback(table="tl_filter_config_element", target="list.sorting.child_record")
     */
    public function onListSortingChildRecordCallback(array $row): string
    {
        $context = [];
        $filterConfigModel = FilterConfigModel::findByPk($row['pid'] ?? 0);

        if ($filterConfigModel) {
            $context['table'] = $filterConfigModel->dataContainer;
        }

        /** @var class-string<AbstractType> $class */
        $class = $this->filterCollection->getClassByType($row['type'] ?? '');

        $attributes = '';
        $enabled = $class::isEnabledForCurrentContext($context);

        return '<div class="tl_content_left">'
            . ($enabled ? '' : Image::getHtml('error.svg', '', 'style="padding-right: 3px;" title="' . $this->translator->trans('huh.filter.warning.not_supported_in_current_content') . '"'))
            . ($row['title'] ?: $row['id']) . ' <span style="color:#b3b3b3; padding-left:3px">[' . ($GLOBALS['TL_LANG']['tl_filter_config_element']['reference']['type'][$row['type']] ?: $row['type']) . ($row['isInitial'] ? ' – Initial' : '') . ']</span></div>';
    }

    /**
     * @Callback(table="tl_filter_config_element", target="fields.type.options")
     */
    public function onTypeOptionsCallback(DataContainer $dc = null): array
    {
        $element = null;

        if ($dc) {
            $element = FilterConfigElementModel::findByPk($dc->id);
        }

        if ($element) {
            $filterConfigModel = FilterConfigModel::findByPk($element->pid);
        }

        return $this->filterCollection->getFilterElementTypes($filterConfigModel->type ?? 'filter', true, [
            'table' => $filterConfigModel->dataContainer ?? 'null',
            'filterConfigElementModel' => $element,
        ]);
    }

    /**
     * @Callback(table="tl_filter_config_element", target="fields.field.options")
     * @Callback(table="tl_filter_config_element", target="fields.fields.options")
     */
    public function onFieldOptionsCallback(DataContainer $dc = null): array
    {
        if (null === ($model = $this->utils->model()->findModelInstanceByPk($dc->table, $dc->id))) {
            return [];
        }

        if (null === ($filterConfig = $this->filterManager->findById($model->pid))) {
            return [];
        }

        $fields = $this->utils->dca()->getDcaFields($filterConfig->getFilter()['dataContainer'], [
            'onlyDatabaseFields' => true,
            'localizeLabels' => true,
        ]);

        $options = [];

        foreach ($fields as $field => $label) {
            $options[$field] = $field . ' <span style="display: inline; color:#999; padding-left:3px">[' . $label . ']</span>';
        }

        return $options;
    }

    /**
     * Support 1 child table atm
     * @Callback(table="tl_filter_config_element", target="fields.childTable.options")
     */
    public function onChildTableOptionsCallback(DataContainer $dc = null): array
    {
        if (null === ($model = $this->utils->model()->findModelInstanceByPk($dc->table, $dc->id))) {
            return [];
        }

        if (null === ($filterConfig = $this->filterManager->findById($model->pid))) {
            return [];
        }

        $options = [];
        $childTables = $GLOBALS['TL_DCA'][$filterConfig->getFilter()['dataContainer']]['config']['ctable'] ?? [];

        foreach ($childTables as $childTable) {
            $options[$childTable] = $childTable;
        }

        return $options;
    }

    /**
     * @Callback(table="tl_filter_config_element", target="fields.childFields.options")
     */
    public function onChildFieldOptionsCallback(DataContainer $dc = null): array
    {
        if (null === ($model = $this->utils->model()->findModelInstanceByPk($dc->table, $dc->id))) {
            return [];
        }

        if(!$model->childTable) {
            return [];
        }

        if (null === ($filterConfig = $this->filterManager->findById($model->pid))) {
            return [];
        }


        $fields = $this->utils->dca()->getDcaFields($model->childTable, [
            'onlyDatabaseFields' => true,
            'localizeLabels' => true,
        ]);

        $options = [];

        foreach ($fields as $field => $label) {
            $options[$field] = $field . ' <span style="display: inline; color:#999; padding-left:3px">[' . $label . ']</span>';
        }

        return $options;
    }

    public function isDublicateName(FilterConfigElementModel $element): bool
    {
        $elements = FilterConfigElementModel::findPublishedByPid($element->pid);
        if (!$elements) {
            return false;
        }

        $type = $this->filterCollection->getClassByType($element->type);
        if (!$type) {
            return false;
        }
        $type = new $type($this->filterConfig);

        $elementName = $type->getName($element);

        foreach ($elements as $sibling) {
            if ($element->id === $sibling->id) {
                continue;
            }
            $siblingType = $this->filterCollection->getClassByType($sibling->type);
            if (!$siblingType) {
                continue;
            }
            $siblingType = new $siblingType($this->filterConfig);
            if ($elementName === $siblingType->getName($sibling)) {
                return true;
            }
        }

        return false;
    }
}
