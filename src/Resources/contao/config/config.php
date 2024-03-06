<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\FilterBundle\ContentElement\ContentFilterHyperlink;

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
 * Content elements
 */
$GLOBALS['TL_CTE']['filter'][ContentFilterHyperlink::TYPE] = ContentFilterHyperlink::class;

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['huh.filter'] = ['huh.filter.listener.inserttag', 'onReplaceInsertTags'];
$GLOBALS['TL_HOOKS']['isBlockVisibleHook']['isBlockVisible'] = ['huh.filter.listener.hooks', 'isBlockVisible'];
