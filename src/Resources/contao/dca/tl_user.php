<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('fop;', 'fop;{filter_legend},filters,filterp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('fop;', 'fop;{filter_legend},filters,filterp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['filters'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['filters'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_filter.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user']['fields']['filterp'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['filterp'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];
