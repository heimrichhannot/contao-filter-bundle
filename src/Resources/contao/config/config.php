<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['filter'] = [
    'tables' => ['tl_filter', 'tl_filter_element']
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_filter']         = 'HeimrichHannot\FilterBundle\Model\FilterModel';
$GLOBALS['TL_MODELS']['tl_filter_element'] = 'HeimrichHannot\FilterBundle\Model\FilterElementModel';

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'filters';
$GLOBALS['TL_PERMISSIONS'][] = 'filterp';
