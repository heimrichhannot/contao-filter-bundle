<?php

\Contao\Controller::loadDataContainer('tl_module');
\Contao\System::loadLanguageFile('tl_module');

$GLOBALS['TL_DCA']['tl_list_config'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => 'tl_list_config_element',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'         => 1,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['edit'],
                'href'  => 'table=tl_list_config_element',
                'icon'  => 'edit.svg',
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_list_config']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'header.svg',
                'button_callback' => ['HeimrichHannot\ListBundle\Backend\ListConfig', 'editHeader'],
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_list_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => [
            'showItemCount',
            'showNoItemsText',
            'limitFields',
            'isTableList',
            'sortingMode',
            'useAlias',
            'addDetails',
            'addShare',
            'addAjaxPagination',
            'addMasonry',
        ],
        'default'      => '{general_legend},title;'.'{filter_legend},filter;'.'{config_legend},numberOfItems,perPage,skipFirst,showItemCount,showNoItemsText,showInitialResults,limitFields,isTableList;'.'{sorting_legend},sortingMode;'.'{jumpto_legend},useAlias,addDetails,addShare;'.'{action_legend},addHashToAction,removeAutoItemFromAction;'.'{misc_legend},addAjaxPagination,addMasonry;'.'{template_legend},listTemplate,itemTemplate;',
    ],
    'subpalettes' => [
        'showItemCount'                                                                  => 'itemCountText',
        'showNoItemsText'                                                                => 'noItemsText',
        'limitFields'                                                                    => 'fields',
        'isTableList'                                                                    => 'tableFields,hasHeader,sortingHeader',
        'sortingMode_'.\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_FIELD => 'sortingField,sortingDirection',
        'sortingMode_'.\HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_TEXT  => 'sortingText',
        'useAlias'                                                                       => 'aliasField',
        'addDetails'                                                                     => 'useModalExplanation,useModal,jumpToDetails',
        'addShare'                                                                       => 'jumpToShare,shareAutoItem',
        'addAjaxPagination'                                                              => 'addInfiniteScroll',
        'addMasonry'                                                                     => 'masonryStampContentElements',
    ],
    'fields'      => [
        'id'                          => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'                      => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'                   => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        // general
        'title'                       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        // config
        'limitFields'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['limitFields'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'fields'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['fields'],
            'inputType'        => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql'              => "blob NULL",
        ],
        'numberOfItems'               => $GLOBALS['TL_DCA']['tl_module']['fields']['numberOfItems'],
        'perPage'                     => $GLOBALS['TL_DCA']['tl_module']['fields']['perPage'],
        'skipFirst'                   => $GLOBALS['TL_DCA']['tl_module']['fields']['skipFirst'],
        'showItemCount'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['showItemCount'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'itemCountText'               => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['itemCountText'],
            'exclude'          => true,
            'search'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.list.count.text');
            },
            'eval'             => ['maxlength' => 64, 'includeBlankOption' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'showNoItemsText'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['showNoItemsText'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'noItemsText'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['noItemsText'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.list.empty.text');
            },
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50', 'includeBlankOption' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'showInitialResults'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['showInitialResults'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'isTableList'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['isTableList'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'hasHeader'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['hasHeader'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'sortingHeader'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['sortingHeader'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'tableFields'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['tableFields'],
            'inputType'        => 'checkboxWizard',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigHelper::getFields($dc);
            },
            'exclude'          => true,
            'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr autoheight'],
            'sql'              => "blob NULL",
        ],
        // filter
        'filter'                      => [
            'label'      => &$GLOBALS['TL_LANG']['tl_list_config']['filter'],
            'exclude'    => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_filter_config.title',
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
            'eval'       => ['tl_class' => 'w50 clr', 'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true, 'submitOnChange' => true],
            'sql'        => "int(10) NOT NULL default '0'",
        ],
        // sorting
        'sortingMode'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['sortingMode'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "varchar(16) NOT NULL default 'field'",
        ],
        'sortingField'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['sortingField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigHelper::getFields($dc);
            },
            'reference'        => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'sortingDirection'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['sortingDirection'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTIONS,
            'reference' => &$GLOBALS['TL_LANG']['tl_list_config']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'sortingText'                 => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['sortingText'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        // jump to
        'useAlias'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['useAlias'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'aliasField'                  => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['aliasField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return \HeimrichHannot\ListBundle\Util\ListConfigHelper::getTextFields($dc);
            },
            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'chosen' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'addDetails'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['addDetails'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'useModal'                    => $GLOBALS['TL_DCA']['tl_module']['fields']['useModal'],
        'useModalExplanation'         => $GLOBALS['TL_DCA']['tl_module']['fields']['useModalExplanation'],
        'jumpToDetails'               => [
            'label'      => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToDetails'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['fieldType' => 'radio', 'tl_class' => 'w50 clr'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'addShare'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['addShare'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'jumpToShare'                 => [
            'label'      => &$GLOBALS['TL_LANG']['tl_list_config']['jumpToShare'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['fieldType' => 'radio', 'tl_class' => 'w50 clr'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'shareAutoItem'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['shareAutoItem'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        // misc
        'addAjaxPagination'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['addAjaxPagination'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addInfiniteScroll'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['addInfiniteScroll'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'addAsDataAttribute' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addMasonry'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['addMasonry'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true, 'addAsDataAttribute' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'masonryStampContentElements' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['masonryStampContentElements'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields'      => [
                        'stampBlock'    => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['stampBlock'],
                            'exclude'          => true,
                            'inputType'        => 'select',
                            'options_callback' => ['HeimrichHannot\Blocks\Backend\Content', 'getBlocks'],
                            'eval'             => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
                        ],
                        'stampCssClass' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_list_config']['stampCssClass'],
                            'exclude'   => true,
                            'search'    => true,
                            'inputType' => 'text',
                            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        // template
        'listTemplate'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['listTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.list.choice.template.list', 'getCachedChoices'],
            'eval'             => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'itemTemplate'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_list_config']['itemTemplate'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.list.choice.template.item', 'getCachedChoices'],
            'eval'             => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
    ],
];