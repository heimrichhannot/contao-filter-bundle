<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['filter'] = [
    'tables' => ['tl_filter', 'tl_filter_element'],
    'option' => ['contao.controller.backend_csv_import', 'importOptionWizard']
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_filter_config']         = 'HeimrichHannot\FilterBundle\Model\FilterConfigModel';
$GLOBALS['TL_MODELS']['tl_filter_config_element'] = 'HeimrichHannot\FilterBundle\Model\FilterConfigElementModel';

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'filters';
$GLOBALS['TL_PERMISSIONS'][] = 'filterp';

/**
 * Front end modules
 */
array_insert(
    $GLOBALS['FE_MOD']['filter'],
    2,
    [
        'filter' => 'HeimrichHannot\FilterBundle\Module\ModuleFilter',
    ]
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['initializeSystem']['huh.filter.registry'] = ['huh.filter.registry', 'init'];