<?php

$dc = &$GLOBALS['TL_DCA']['tl_module'];

/**
 * Palettes
 */
$dc['palettes'][HeimrichHannot\FilterBundle\Module\ModuleFilter::TYPE] = '{title_legend},name,headline,type;{config_legend},filter;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields
 */
$fields = [
    'filter' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_module']['filter'],
        'exclude'    => true,
        'inputType'  => 'select',
        'foreignKey' => 'tl_filter_config.title',
        'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval'       => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'mandatory' => true, 'chosen' => true],
        'sql'        => "int(10) NOT NULL default '0'",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);
