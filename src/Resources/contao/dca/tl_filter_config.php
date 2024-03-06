<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\FilterBundle\Backend\FilterConfig;

$GLOBALS['TL_DCA']['tl_filter_config'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_filter_config_element'],
        'switchToEdit' => true,
        'enableVersioning' => true,
        'onload_callback' => [
            [FilterConfig::class, 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'start,stop,published' => 'index',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 2,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['edit'],
                'href' => 'table=tl_filter_config_element',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
                'button_callback' => [FilterConfig::class, 'editHeader'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
                'button_callback' => [FilterConfig::class, 'copyArchive'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['copy'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => [FilterConfig::class, 'deleteArchive'],
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [FilterConfig::class, 'toggleIcon'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['published', 'type'],
        'default' => '{general_legend},title,type;',
        'filter' => '{general_legend},title,type;{config_legend},authorType,author,name,dataContainer,method,filterFormAction,renderEmpty,mergeData,asyncFormSubmit,resetFilterInitial;{template_legend},template;{expert_legend},cssClass;{publish_legend},published;',
        'sort' => '{general_legend},title,type;{config_legend},parentFilter;{template_legend},template;{expert_legend},cssClass;{publish_legend},published;',
    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['name'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'maxlength' => 64, 'rgxp' => 'fieldname'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'dataContainer' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['dataContainer'],
            'options_callback' => ['huh.utils.choice.data_container', 'getChoices'],
            'eval' => [
                'chosen' => true,
                'submitOnChange' => true,
                'includeBlankOption' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
            ],
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'method' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['method'],
            'options' => ['GET', 'POST'],
            'default' => 'GET',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'exclude' => true,
            'sql' => "varchar(4) NOT NULL default ''",
        ],
        'filterFormAction' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['filterFormAction'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'renderEmpty' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['renderEmpty'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'template' => [
            'inputType' => 'select',
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['template'],
            'options_callback' => function (DataContainer $dc) {
                return System::getContainer()->get('huh.filter.choice.template')->getCachedChoices($dc);
            },
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'exclude' => true,
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'cssClass' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['cssClass'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 64],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'mergeData' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['mergeData'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['type'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\FilterBundle\Config\FilterConfig::FILTER_TYPES,
            'eval' => [
                'tl_class' => 'w50',
                'submitOnChange' => true,
                'mandatory' => true,
            ],
            'sql' => "varchar(64) NOT NULL default 'filter'",
        ],
        'parentFilter' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['parentFilter'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_filter_config.title',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'eval' => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'asyncFormSubmit' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['asyncFormSubmit'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'resetFilterInitial' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config']['resetFilterInitial'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
