<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('fop;', 'fop;{filter_legend},filters,filterp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['filters'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['filters'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_filter_config.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['filterp'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['filterp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];