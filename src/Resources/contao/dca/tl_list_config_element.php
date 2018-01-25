<?php

\Contao\Controller::loadDataContainer('tl_module');

$GLOBALS['TL_DCA']['tl_list_config_element'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_list_config',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'                  => 1,
            'fields'                => ['title'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['HeimrichHannot\ListBundle\Backend\ListConfigElement', 'listChildren']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_list_config_element']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__'                                                   => [
            'type',
            'placeholderImageMode'
        ],
        'default'                                                        => '{type_legend},title,type;',
        \HeimrichHannot\ListBundle\Backend\ListConfigElement::TYPE_IMAGE => '{title_type_legend},title,type;{config_legend},imageSelectorField,imageField,imgSize,placeholderImageMode;'
    ],
    'subpalettes' => [
        'placeholderImageMode_' . \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE   => 'placeholderImage',
        'placeholderImageMode_'
        . \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED                         => 'genderField,placeholderImage,placeholderImageFemale',
    ],
    'fields'      => [
        'id'                   => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'                  => [
            'foreignKey' => 'tl_list_config.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'tstamp'               => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'            => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'type'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ListBundle\Backend\ListConfigElement::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config_element']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'imageSelectorField'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['imageSelectorField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc)
            {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getCheckboxFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imageField'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['imageField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc)
            {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'imgSize'              => $GLOBALS['TL_DCA']['tl_module']['fields']['imgSize'],
        'placeholderImageMode' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImageMode'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config_element']['reference'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'placeholderImage'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImage'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['tl_class' => 'w50 autoheight', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => "binary(16) NULL"
        ],
        'placeholderImageFemale'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['placeholderImageFemale'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['tl_class' => 'w50 autoheight', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => Config::get('validImageTypes'), 'mandatory' => true],
            'sql'       => "binary(16) NULL"
        ],
        'genderField'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config_element']['genderField'],
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc)
            {
                return \HeimrichHannot\ListBundle\Util\ListConfigElementHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['includeBlankOption' => true, 'mandatory' => true, 'chosen' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
    ]
];