<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['BE_MOD']['system']['filter'] = [
    'tables' => ['tl_filter_config', 'tl_filter_config_element'],
    'option' => ['contao.controller.backend_csv_import', 'importOptionWizard'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_filter_config'] = 'HeimrichHannot\FilterBundle\Model\FilterConfigModel';
$GLOBALS['TL_MODELS']['tl_filter_config_element'] = 'HeimrichHannot\FilterBundle\Model\FilterConfigElementModel';
$GLOBALS['TL_MODELS']['tl_filter_preselect'] = 'HeimrichHannot\FilterBundle\Model\FilterPreselectModel';

/*
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'filters';
$GLOBALS['TL_PERMISSIONS'][] = 'filterp';

/*
 * Front end modules
 */
array_insert(
    $GLOBALS['FE_MOD']['filter'],
    2,
    [
        HeimrichHannot\FilterBundle\Module\ModuleFilter::TYPE => HeimrichHannot\FilterBundle\Module\ModuleFilter::class,
    ]
);

/*
 * Content elements
 */
$GLOBALS['TL_CTE']['filter']['filter_preselect'] = \HeimrichHannot\FilterBundle\ContentElement\ContentFilterPreselect::class;
$GLOBALS['TL_CTE']['filter']['filter_hyperlink'] = \HeimrichHannot\FilterBundle\ContentElement\ContentFilterHyperlink::class;

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['huh.filter'] = ['huh.filter.listener.inserttag', 'onReplaceInsertTags'];
$GLOBALS['TL_HOOKS']['isBlockVisibleHook']['isBlockVisible'] = ['huh.filter.listener.hooks', 'isBlockVisible'];
