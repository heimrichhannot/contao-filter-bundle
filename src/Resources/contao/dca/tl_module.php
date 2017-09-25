<?php

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dc['palettes']['newslist'] = str_replace('type;', 'type;{filter_legend},filter', $dc['palettes']['newslist']);

/**
 * Fields
 */
$fields = [
//    'filter' => [
//        'label'      => &$GLOBALS['TL_LANG']['tl_module']['filter'],
//        'exclude'    => true,
//        'inputType'  => 'select',
//        'foreignKey' => 'tl_filter.title',
//        'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
//        'eval'       => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
//        'sql'        => "int(10) NOT NULL default '0'",
//    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);