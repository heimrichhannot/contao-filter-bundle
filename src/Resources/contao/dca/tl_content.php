<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\FilterBundle\Controller\ContentElement\FilterPreselectElementController;

$dc = &$GLOBALS['TL_DCA']['tl_content'];

/*
 * Palettes
 */
$dc['palettes']['__selector__'][] = 'filter';
$dc['palettes'][FilterPreselectElementController::TYPE] = '{type_legend},type;{filter_legend},filterConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests;{invisible_legend:hide},invisible,start,stop';
$dc['palettes']['filter_hyperlink'] = '{type_legend},type,headline;{filter_legend},filterConfig;{link_legend},target,linkTitle,embed,titleText,rel;{imglink_legend:hide},useImage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

/**
 * Fields.
 */
$fields = [
    'filterConfig' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['filterConfig'],
        'exclude' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_filter_config.title',
        'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        'eval' => ['tl_class' => 'w50 clr', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true, 'chosen' => true],
        'sql' => "int(10) NOT NULL default '0'",
    ],
    'filterPreselect' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['filterPreselect'],
        'inputType' => 'fieldpalette',
        'foreignKey' => 'tl_filter_preselect.id',
        'relation' => ['type' => 'hasMany', 'load' => 'eager'],
        'eval' => ['tl_class' => 'clr'],
        'sql' => 'blob NULL',
        'fieldpalette' => [
            'config' => [
                'hidePublished' => false,
                'table' => 'tl_filter_preselect',
            ],
            'list' => [
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
        'label' => &$GLOBALS['TL_LANG']['tl_content']['filterReset'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'sql' => "char(1) NOT NULL default ''",
    ],
    'filterPreselectNoRedirect' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['filterPreselectNoRedirect'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['doNotCopy' => true],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'filterPreselectJumpTo' => [
        'label' => &$GLOBALS['TL_LANG']['tl_content']['filterPreselectJumpTo'],
        'exclude' => true,
        'inputType' => 'pageTree',
        'foreignKey' => 'tl_page.title',
        'eval' => ['fieldType' => 'radio', 'tl_class' => 'w50'],
        'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);
