<?php

use Symfony\Component\Intl\Intl;

$GLOBALS['TL_DCA']['tl_filter_config_element'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_filter_config',
        'enableVersioning'  => true,
        'switchToEdit'      => true,
        'onload_callback'   => [
            ['huh.filter.backend.filter_config_element', 'checkPermission'],
            ['huh.filter.backend.filter_config_element', 'modifyPalette'],
            ['huh.filter.backend.filter_config_element', 'prepareChoiceTypes'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id'                               => 'primary',
                'pid,start,stop,published,sorting' => 'index',
            ],
        ],
    ],
    'list'        => [
        'label'             => [
            'fields' => ['id'],
            'format' => '%s',
        ],
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['sorting'],
            'headerFields'          => ['title', 'published', 'start', 'stop'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['huh.filter.backend.filter_config_element', 'listElements'],
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
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.svg',
            ],
            'cut'    => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_filter_config_element']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filter_config_element']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['huh.filter.backend.filter_config_element', 'toggleIcon'],
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => [
            'type',
            'customOptions',
            'adjustOptionLabels',
            'customName',
            'customOperator',
            'addPlaceholder',
            'customLabel',
            'customCountries',
            'customLanguages',
            'customLocales',
            'customValue',
            'initialValueType',
            'addDefaultValue',
            'defaultValueType',
            'addStartAndStop',
            'inputGroup',
            'published',
            'addParentSelector',
            'alternativeValueSource',
            'addGroupChoiceField',
            'modifyGroupChoices',
            'addOptionCount',
            'sourceTable'
        ],
        'default'      => '{general_legend},title,type,isInitial;{publish_legend},published;',
        'text'         => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\TextConcatType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},fields,name;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        'textarea'     => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\EmailType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\IntegerType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,grouping,scale,rounding_mode;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\MoneyType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,currency,divisor,grouping,scale;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\NumberType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,grouping,scale,roundingMode;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\PasswordType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,alwaysEmpty;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        'search'       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\PercentType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,scale,percentType;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        'url'          => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,defaultProtocol;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        'range'        => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,min,max,step;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        'tel'          => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\ColorType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\ChoiceType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,multiple,submitOnChange,addGroupChoiceField;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\CountryType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customCountries,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,multiple;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\LanguageType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customLanguages,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,multiple;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\LocaleType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customLocales,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,multiple;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\ParentType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,multiple;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\SkipParentsType::TYPE
                       => '{general_legend},title,type;{config_legend},parentField,customName;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\PublishedType::TYPE
                       => '{general_legend},title,type;{config_legend},field,customName,invertField,ignoreFePreview,addStartAndStop;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\HiddenType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\ButtonType::TYPE
                       => '{general_legend},title,type;{config_legend},name,label;{expert_legend},cssClass;{publish_legend},published;',
        'reset'        => '{general_legend},title,type;{config_legend},customName,alwaysShow;{visualization_legend},customLabel,hideLabel;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\SubmitType::TYPE
                       => '{general_legend},title,type;{config_legend},customName;{visualization_legend},customLabel,hideLabel;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\CheckboxType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,customValue,submitOnChange;{visualization_legend},customLabel,hideLabel;{expert_legend},cssClass;{publish_legend},published;',
        'radio'        => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,customValue,submitOnChange;{visualization_legend},customLabel,hideLabel;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\DateType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,name,customValue,dateWidget,dateFormat,html5,minDate,maxDate;{visualization_legend},customLabel,hideLabel,inputGroup,addPlaceholder;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\DateTimeType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,name,customValue,dateWidget,timeWidget,html5,dateTimeFormat,minDateTime,maxDateTime;{visualization_legend},customLabel,hideLabel,inputGroup,addPlaceholder;{expert_legend},cssClass;{publish_legend},published;',
        'time'         => '{general_legend},title,type,isInitial;{config_legend},field,name,customValue,timeWidget,timeFormat,minTime,html5,maxTime;{visualization_legend},customLabel,hideLabel,inputGroup,addPlaceholder;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\DateRangeType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},startElement,stopElement,name;{visualization_legend},customLabel,hideLabel;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\MultipleRangeType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},startElement,stopElement,name,submitOnChange;{visualization_legend},customLabel,hideLabel;{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\SqlType::TYPE
                       => '{general_legend},title,type;{config_legend},whereSql;{publish_legend},published',
        \HeimrichHannot\FilterBundle\Filter\Type\YearType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,submitOnChange,minDate,maxDate,dynamicOptions,addOptionCount;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\DateChoiceType::TYPE
                       => '{general_legend},title,type,isInitial;{config_legend},field,customOptions,adjustOptionLabels,reviseOptions,sortOptionValues,customName,customOperator,addDefaultValue,expanded,submitOnChange,minDate,maxDate,dynamicOptions,dateFormat;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup{expert_legend},cssClass;{publish_legend},published;',
        \HeimrichHannot\FilterBundle\Filter\Type\AutoItemType::TYPE
                       => '{general_legend},title,type;{config_legend},field,customOperator;{publish_legend},published',
        \HeimrichHannot\FilterBundle\Filter\Type\SortType::TYPE
                       => '{general_legend},title,type;{config_legend},sortOptions,expanded,submitOnChange;{visualization_legend},addPlaceholder,customLabel,hideLabel;{publish_legend},published',
        \HeimrichHannot\FilterBundle\Filter\Type\ExternalEntityType::TYPE
            => '{general_legend},title,type;{source_legend},sourceTable,sourceField,sourceEntityResolve,sourceEntityOverridesOrder;{config_legend},field,customOperator;{publish_legend},published;',
],
    'subpalettes' => [
        'customOptions'       => 'options',
        'adjustOptionLabels'  => 'optionLabelPattern',
        'addPlaceholder'      => 'placeholder',
        'customName'          => 'name',
        'customOperator'      => 'operator',
        'customLabel'         => 'label',
        'customCountries'     => 'countries',
        'customLanguages'     => 'languages',
        'customLocales'       => 'locales',
        'customValue'         => 'value',
        'initialValueType_' . \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR
                              => 'initialValue',
        'initialValueType_' . \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY
                              => 'initialValueArray',
        'initialValueType_' . \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_LATEST
                              => 'parentField',
        'addDefaultValue'     => 'defaultValueType',
        'defaultValueType_' . \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR
                              => 'defaultValue',
        'defaultValueType_' . \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY
                              => 'defaultValueArray',
        'defaultValueType_' . \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_LATEST
                              => 'parentField',
        'addStartAndStop'     => 'startField,stopField',
        'addParentSelector'   => 'parentField',
        'inputGroup'          => 'inputGroupPrepend,inputGroupAppend',
        'addGroupChoiceField' => 'modifyGroupChoices',
        'modifyGroupChoices'  => 'groupChoices',
        'addOptionCount'      => 'optionCountLabel',
        'published'           => 'start,stop',
    ],
    'fields'      => [
        'id'                     => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                    => [
            'foreignKey' => 'tl_filter_config.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'sorting'                => [
            'sorting' => true,
            'flag'    => 2,
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'                 => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded'              => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'",
        ],
        'type'                   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['type'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.choice.type')->getCachedChoices($dc);
            },
            'reference'        => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference']['type'],
            'eval'             => [
                'chosen'             => true,
                'tl_class'           => 'w50',
                'submitOnChange'     => true,
                'mandatory'          => true,
                'includeBlankOption' => true,
            ],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'isInitial'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['isInitial'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'title'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'field'                  => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['field'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getFields($dc);
            },
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'fields'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['fields'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getFields($dc);
            },
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'multiple' => true, 'mandatory' => true],
            'sql'              => "blob NULL",
        ],
        'parentField'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['parentField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getFields($dc);
            },
            'eval'             => ['chosen' => true, 'includeBlankOption' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'customOptions'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customOptions'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'reviseOptions'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reviseOptions'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'adjustOptionLabels'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['adjustOptionLabels'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'optionLabelPattern'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['optionLabelPattern'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.option_label');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'options'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['options'],
            'exclude'   => true,
            'inputType' => 'optionWizard',
            'eval'      => ['mandatory' => true, 'allowHtml' => true],
            'xlabel'    => [
                ['huh.filter.backend.filter_config_element', 'optionImportWizard'],
            ],
            'sql'       => "blob NULL",
        ],
        'sortOptionValues'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptionValues'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'customName'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customName'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'name'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['name'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'doNotCopy' => true, 'rgxp' => 'fieldname', 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'customOperator'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customOperator'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'operator'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['operator'],
            'inputType' => 'select',
            'options'   => \HeimrichHannot\UtilsBundle\Database\DatabaseUtil::OPERATORS,
            'reference' => &$GLOBALS['TL_LANG']['MSC']['databaseOperators'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'addPlaceholder'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addPlaceholder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'placeholder'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['placeholder'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.placeholder');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'hideLabel'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['hideLabel'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'customLabel'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customLabel'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'label'                  => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['label'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.label');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'timeFormat'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['timeFormat'],
            'exclude'   => true,
            'default'   => \Contao\Config::get('timeFormat'),
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'dateFormat'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['dateFormat'],
            'exclude'   => true,
            'default'   => \Contao\Config::get('dateFormat'),
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "varchar(18) NOT NULL default ''",
        ],
        'dateTimeFormat'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['dateTimeFormat'],
            'exclude'   => true,
            'default'   => \Contao\Config::get('datimFormat'),
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'clr w50'],
            'sql'       => "varchar(28) NOT NULL default ''",
        ],
        'startElement'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['startElement'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                if (null === ($model = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
                    return [];
                }

                $context = [
                    'pid'   => $model->pid
                ];

                switch ($model->type)
                {
                    case \HeimrichHannot\FilterBundle\Filter\Type\DateRangeType::TYPE:
                        $context['types'] = ['date', 'time', 'date_time'];

                        break;
                    case \HeimrichHannot\FilterBundle\Filter\Type\MultipleRangeType::TYPE:
                        $context['types'] = ['text'];

                        break;
                }

                return \Contao\System::getContainer()->get('huh.filter.choice.element')->getCachedChoices($context);
            },
            'eval'             => ['chosen' => true, 'tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'stopElement'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['stopElement'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                if (null === ($model = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
                    return [];
                }

                $context = [
                    'pid'   => $model->pid
                ];

                if ($model->type === \HeimrichHannot\FilterBundle\Filter\Type\DateRangeType::TYPE) {
                    $context['types'] = ['date', 'time', 'date_time'];
                }

                return \Contao\System::getContainer()->get('huh.filter.choice.element')->getCachedChoices($context);
            },
            'eval'             => ['chosen' => true, 'tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'minDateTime'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['minDateTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 clr', 'maxlength' => 32],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'maxDateTime'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['maxDateTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 32],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'minDate'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['minDate'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 clr', 'maxlength' => 32],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'maxDate'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['maxDate'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 32],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'minTime'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['minTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 32],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'maxTime'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['maxTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 32],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'dateWidget'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['dateWidget'],
            'exclude'   => true,
            'inputType' => 'select',
            'default'   => \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_SINGLE_TEXT,
            'options'   => [
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_CHOICE,
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_TEXT,
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_SINGLE_TEXT,
            ],
            'eval'      => ['tl_class' => 'w50', 'chosen' => true, 'submitOnChange' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'timeWidget'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['timeWidget'],
            'exclude'   => true,
            'inputType' => 'select',
            'default'   => \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_SINGLE_TEXT,
            'options'   => [
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_CHOICE,
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_TEXT,
                \HeimrichHannot\FilterBundle\Filter\Type\DateType::WIDGET_TYPE_SINGLE_TEXT,
            ],
            'eval'      => ['tl_class' => 'w50', 'chosen' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'html5'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['html5'],
            'exclude'   => true,
            'default'   => false,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'inputGroup'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['inputGroup'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'inputGroupPrepend'      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['inputGroupPrepend'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.input_group_text');
            },
            'eval'             => ['chosen' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'inputGroupAppend'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['inputGroupAppend'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.input_group_text');
            },
            'eval'             => ['chosen' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'expanded'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['expanded'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'multiple'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['multiple'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'grouping'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['grouping'],
            'exclude'   => true,
            'default'   => false,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'scale'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['scale'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => 0,
            'eval'      => ['tl_class' => 'clr w50', 'rgxp' => 'natural', 'maxlength' => 2],
            'sql'       => "int(2) unsigned NOT NULL default '0'",
        ],
        'roundingMode'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['roundingMode'],
            'exclude'   => true,
            'inputType' => 'select',
            'default'   => \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_DOWN,
            'options'   => [
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_DOWN,
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_FLOOR,
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_UP,
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_CEILING,
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_DOWN,
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN,
                \Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_HALF_UP,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference']['roundingMode'],
            'eval'      => ['tl_class' => 'w50 wizard', 'rgxp' => 'natural', 'maxlength' => 2],
            'sql'       => "int(2) unsigned NOT NULL default '0'",
        ],
        'currency'               => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['currency'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'EUR',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencyNames();
            },
            'eval'             => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'maxlength' => 3],
            'sql'              => "varchar(3) NOT NULL default ''",
        ],
        'divisor'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['divisor'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => 1,
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 10],
            'sql'       => "int(10) unsigned NOT NULL default '1'",
        ],
        'alwaysEmpty'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['alwaysEmpty'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'percentType'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['percentType'],
            'inputType' => 'select',
            'exclude'   => true,
            'default'   => 'fractional',
            'options'   => ['fractional', 'integer'],
            'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference']['percentType'],
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 10],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'defaultProtocol'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultProtocol'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => 'http',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 12],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'min'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['min'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 12, 'mandatory' => true],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'max'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['max'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 12, 'mandatory' => true],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'step'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['step'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 12, 'mandatory' => true],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'customCountries'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customCountries'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'doNotCopy' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'countries'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['countries'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'EUR',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return Symfony\Component\Intl\Intl::getRegionBundle()->getCountryNames();
            },
            'eval'             => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'multiple' => true, 'mandatory' => true],
            'sql'              => "blob NULL",
        ],
        'customLanguages'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customLanguages'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'languages'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['languages'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'EUR',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return Symfony\Component\Intl\Intl::getLanguageBundle()->getLanguageNames();
            },
            'eval'             => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'multiple' => true, 'mandatory' => true],
            'sql'              => "blob NULL",
        ],
        'customLocales'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customLocales'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'locales'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['locales'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'EUR',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return Symfony\Component\Intl\Intl::getLocaleBundle()->getLocaleNames();
            },
            'eval'             => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'multiple' => true, 'mandatory' => true],
            'sql'              => "blob NULL",
        ],
        'customValue'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customValue'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'value'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['value'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'clr w50 wizard', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'initialValueType'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValueType'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.filter.listener.dca.callback.filterconfigelement', 'getValueTypeOptions'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference'],
            'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'              => "varchar(16) NOT NULL default ''",
        ],
        'initialValue'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValue'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'initialValueArray'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValue'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'value' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValue_value'],
                            'exclude'   => true,
                            'search'    => true,
                            'inputType' => 'text',
                            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'groupStyle' => 'width: 200px'],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'addDefaultValue'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addDefaultValue'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'defaultValueType'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValueType'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => ['huh.filter.listener.dca.callback.filterconfigelement', 'getValueTypeOptions'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference'],
            'eval'             => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'              => "varchar(16) NOT NULL default ''",
        ],
        'defaultValue'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValue'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'defaultValueArray'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValue'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'value' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValue_value'],
                            'exclude'   => true,
                            'search'    => true,
                            'inputType' => 'text',
                            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'groupStyle' => 'width: 200px'],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'invertField'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['invertField'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'ignoreFePreview'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['ignoreFePreview'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'addStartAndStop'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addStartAndStop'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'startField'             => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['startField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getFields($dc);
            },
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'stopField'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['stopField'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'select',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getFields($dc);
            },
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'              => "varchar(64) NOT NULL default ''",
        ],
        'cssClass'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['cssClass'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'published'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'whereSql'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['whereSql'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => ['rte' => 'ace|sql', 'tl_class' => 'clr', 'decodeEntities' => true],
            'sql'       => "text NULL",
        ],
        'sortOptions'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptions'],
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'tl_class'          => 'long clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'class'     => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptions_class'],
                            'exclude'          => true,
                            'search'           => true,
                            'inputType'        => 'select',
                            'options_callback' => function (\Contao\DataContainer $dc) {
                                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getSortClasses($dc);
                            },
                            'eval'             => [
                                'tl_class'           => 'w50',
                                'mandatory'          => true,
                                'groupStyle'         => 'width: 300px',
                                'chosen'             => true,
                                'includeBlankOption' => true
                            ],
                        ],
                        'field'     => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptions_field'],
                            'exclude'          => true,
                            'filter'           => true,
                            'inputType'        => 'select',
                            'options_callback' => function (\Contao\DataContainer $dc) {
                                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getFields($dc);
                            },
                            'eval'             => [
                                'tl_class'           => 'w50',
                                'mandatory'          => true,
                                'groupStyle'         => 'width: 150px',
                                'chosen'             => true,
                                'includeBlankOption' => true
                            ],
                        ],
                        'direction' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptions_direction'],
                            'exclude'          => true,
                            'filter'           => true,
                            'inputType'        => 'select',
                            'options_callback' => function (\Contao\DataContainer $dc) {
                                return \Contao\System::getContainer()->get('huh.filter.util.filter_config_element')->getSortDirections($dc);
                            },
                            'eval'             => [
                                'tl_class'           => 'w50',
                                'mandatory'          => true,
                                'groupStyle'         => 'width: 150px',
                                'chosen'             => true,
                                'includeBlankOption' => true
                            ],
                        ],
                        'fieldText' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptions_fieldText'],
                            'exclude'          => true,
                            'filter'           => true,
                            'inputType'        => 'select',
                            'options_callback' => function (\Contao\DataContainer $dc) {
                                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.sort.text');
                            },
                            'eval'             => [
                                'tl_class'           => 'w50',
                                'mandatory'          => true,
                                'groupStyle'         => 'width: 300px',
                                'chosen'             => true,
                                'includeBlankOption' => true
                            ],
                        ],
                        'standard'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sortOptions_standard'],
                            'exclude'   => true,
                            'search'    => true,
                            'inputType' => 'checkbox',
                            'eval'      => ['tl_class' => 'w50', 'groupStyle' => 'width: 100px'],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'submitOnChange'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['submitOnChange'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'alwaysShow'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['alwaysShow'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'dynamicOptions'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['dynamicOptions'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'alternativeValueSource' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['alternativeValueSource'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                // can be set in other bundles
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'addGroupChoiceField'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addGroupChoiceField'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'modifyGroupChoices'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['modifyGroupChoices'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'groupChoices'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['modifyGroupChoices'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.filter.backend.filter_config_element', 'getOptions'],
            'eval'             => ['tl_class' => 'w50', 'multiple' => true, 'mandatory' => true],
            'sql'              => "blob NULL",
        ],
        'addOptionCount'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addOptionCount'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50 clr', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'optionCountLabel'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['optionCountLabel'],
            'exclude'          => true,
            'inputType'        => 'select',
            'default'          => 'huh.filter.option_count.default',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.option_count');
            },
            'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'              => "varchar(128) NOT NULL default ''",
        ],
        'sourceTable' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sourceTable'],
            'inputType' => 'select',
            'options_callback' => ['huh.utils.dca', 'getDataContainers'],
            'eval' => [
                'includeBlankOption' => true,
                'mandatory' => true,
                'submitOnChange' => true,
                'tl_class' => 'clr w50'
            ],
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'sourceField' => [
            'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['sourceField'],
            'inputType' => 'select',
            'options_callback' => ['huh.filter.backend.filter_config_element', 'getSourceFields'],
            'eval' => [
                'includeBlankOption' => true,
                'mandatory' => true,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(32) NOT NULL default ''"
        ]
    ],
];

\Contao\System::getContainer()->get('huh.entity_filter.manager')->addFilterToDca(
    'sourceEntityResolve',
    'tl_filter_config_element',
    ''
);

$GLOBALS['TL_DCA']['tl_filter_config_element']['fields']['sourceEntityResolve']['eval']['tl_class'] = 'clr';