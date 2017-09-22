<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['system']['filter'] = [
    'tables' => ['tl_filter', 'tl_filter_element']
];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'filters';
$GLOBALS['TL_PERMISSIONS'][] = 'filterp';
