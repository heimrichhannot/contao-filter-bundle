<?php

$dc = &$GLOBALS['TL_DCA']['tl_content'];

$dc['config']['onload_callback'][] = ['huh.filter.backend.content', 'onLoad'];

/**
 * Palettes
 */
$dc['palettes']['__selector__'][]   = 'filter';
$dc['palettes']['filter_preselect'] = '{type_legend},type;{filter_legend},filterConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';
$dc['palettes']['filter_hyperlink'] = '{type_legend},type,headline;{filter_legend},filterConfig;{link_legend},target,linkTitle,embed,titleText,rel;{imglink_legend:hide},useImage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields
 */
$fields = [
    'filterConfig'              => [
        'label'      => &$GLOBALS['TL_LANG']['tl_content']['filterConfig'],
        'exclude'    => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_filter_config.title',
        'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval'       => ['tl_class' => 'w50 clr', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true],
        'sql'        => "int(10) NOT NULL default '0'",
    ],
    'filterPreselect'           => [
        'label'        => &$GLOBALS['TL_LANG']['tl_content']['filterPreselect'],
        'inputType'    => 'fieldpalette',
        'foreignKey'   => 'tl_filter_preselect.id',
        'relation'     => ['type' => 'hasMany', 'load' => 'eager'],
        'eval'         => ['tl_class' => 'clr'],
        'sql'          => "blob NULL",
        'fieldpalette' => [
            'config'   => [
                'hidePublished' => false,
                'table'         => 'tl_filter_preselect',
            ],
            'list'     => [
                'label' => [
                    'fields' => ['id'],
                    'format' => '%s',
                ],
            ],
            'palettes' => [
                'default' => '{config_legend},element,initialValueType',
            ],
        ],
    ],
    'filterReset' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['filterReset'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'filterPreselectNoRedirect' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['filterPreselectNoRedirect'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['doNotCopy' => true],
        'sql'       => "char(1) NOT NULL default ''",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);