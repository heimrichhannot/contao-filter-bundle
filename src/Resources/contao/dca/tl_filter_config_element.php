<?php

$GLOBALS['TL_DCA']['tl_filter_config_element'] = [
	'config'      => [
		'dataContainer'     => 'Table',
		'ptable'            => 'tl_filter_config',
		'enableVersioning'  => true,
		'onload_callback'   => [
			['tl_filter_config_element', 'checkPermission'],
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
			'child_record_callback' => ['tl_filter_config_element', 'listElements'],
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
				'href'  => 'act=copy',
				'icon'  => 'copy.gif',
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
				'button_callback' => ['tl_filter_config_element', 'toggleIcon'],
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
			'customName',
			'addPlaceholder',
			'customLabel',
			'dateTimeFormat',
			'customCountries',
			'customLanguages',
			'customLocales',
			'customValue',
			'published'
		],
		'default'      => '{general_legend},title,type;{publish_legend},published;',
		'initial'      => '{general_legend},title,type;{config_legend},field;{publish_legend},published;',
		'text'         => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'text_concat'  => '{general_legend},title,type;{config_legend},fields,name,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'textarea'     => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'email'        => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'integer'      => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,grouping,scale,rounding_mode;{expert_legend},cssClass;{publish_legend},published;',
		'money'        => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,currency,divisor,grouping,scale;{expert_legend},cssClass;{publish_legend},published;',
		'number'       => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,grouping,scale,roundingMode;{expert_legend},cssClass;{publish_legend},published;',
		'password'     => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,alwaysEmpty;{expert_legend},cssClass;{publish_legend},published;',
		'search'       => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'percent'      => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,scale,percentType;{expert_legend},cssClass;{publish_legend},published;',
		'url'          => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,defaultProtocol;{expert_legend},cssClass;{publish_legend},published;',
		'range'        => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel,min,max,step;{expert_legend},cssClass;{publish_legend},published;',
		'tel'          => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'color'        => '{general_legend},title,type;{config_legend},field,customName,addPlaceholder,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'choice'       => '{general_legend},title,type;{config_legend},field,customOptions,customName,addPlaceholder,customLabel,expanded,multiple;{expert_legend},cssClass;{publish_legend},published;',
		'country'      => '{general_legend},title,type;{config_legend},field,customCountries,customOptions,customName,addPlaceholder,customLabel,expanded,multiple;{expert_legend},cssClass;{publish_legend},published;',
		'language'     => '{general_legend},title,type;{config_legend},field,customLanguages,customOptions,customName,addPlaceholder,customLabel,expanded,multiple;{expert_legend},cssClass;{publish_legend},published;',
		'locale'       => '{general_legend},title,type;{config_legend},field,customLocales,customOptions,customName,addPlaceholder,customLabel,expanded,multiple;{expert_legend},cssClass;{publish_legend},published;',
		'hidden'       => '{general_legend},title,type;{config_legend},field,customName;{expert_legend},cssClass;{publish_legend},published;',
		'button'       => '{general_legend},title,type;{config_legend},name,label;{expert_legend},cssClass;{publish_legend},published;',
		'reset'        => '{general_legend},title,type;{config_legend},customName,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'submit'       => '{general_legend},title,type;{config_legend},customName,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'checkbox'     => '{general_legend},title,type;{config_legend},field,customName,customValue,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'radio'        => '{general_legend},title,type;{config_legend},field,customName,customValue,customLabel;{expert_legend},cssClass;{publish_legend},published;',
		'date'         => '{general_legend},title,type;{config_legend},startField,endField,name,customValue,customLabel,dateTimeFormat;{expert_legend},cssClass;{publish_legend},published;',
	],
	'subpalettes' => [
		'customOptions'        => 'options',
		'addPlaceholder'       => 'placeholder',
		'customName'           => 'name',
		'customLabel'          => 'label',
		'addDateTimePicker'    => 'dateTimeRgxp',
		'customCountries'      => 'countries',
		'customLanguages'      => 'languages',
		'customLocales'        => 'locales',
		'customValue'          => 'value',
		'published'            => 'start,stop',
		'dateTimeFormat_date'  => 'minDate,maxDate',
		'dateTimeFormat_time'  => 'minTime,maxTime',
		'dateTimeFormat_datim' => 'minDate,maxDate,minTime,maxTime',
	],
	'fields'      => [
		'id'              => [
			'sql' => "int(10) unsigned NOT NULL auto_increment",
		],
		'pid'             => [
			'foreignKey' => 'tl_filter_config.title',
			'sql'        => "int(10) unsigned NOT NULL default '0'",
			'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
		],
		'sorting'         => [
			'sorting' => true,
			'flag'    => 2,
			'sql'     => "int(10) unsigned NOT NULL default '0'",
		],
		'tstamp'          => [
			'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['tstamp'],
			'sql'   => "int(10) unsigned NOT NULL default '0'",
		],
		'dateAdded'       => [
			'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
			'sorting' => true,
			'flag'    => 6,
			'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
			'sql'     => "int(10) unsigned NOT NULL default '0'",
		],
		'type'            => [
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
				'includeBlankOption' => true
			],
			'sql'              => "varchar(64) NOT NULL default ''",
		],
		'title'           => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['title'],
			'exclude'   => true,
			'search'    => true,
			'sorting'   => true,
			'flag'      => 1,
			'inputType' => 'text',
			'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
			'sql'       => "varchar(255) NOT NULL default ''",
		],
		'field'           => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['field'],
			'exclude'          => true,
			'filter'           => true,
			'inputType'        => 'select',
			'options_callback' => function (\Contao\DataContainer $dc) {
				if (null === ($model = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
					return [];
				}
				
				if (null === ($filterConfig = \Contao\System::getContainer()->get('huh.filter.registry')->findById($model->pid))) {
					return [];
				}
				
				return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
					[
						'dataContainer' => $filterConfig->getFilter()['dataContainer'],
					]
				);
			},
			'eval'             => ['chosen' => true, 'includeBlankOption' => true],
			'sql'              => "varchar(64) NOT NULL default ''",
		],
		'fields'          => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['fields'],
			'exclude'          => true,
			'filter'           => true,
			'inputType'        => 'checkboxWizard',
			'options_callback' => function (\Contao\DataContainer $dc) {
				if (null === ($model = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
					return [];
				}
				
				if (null === ($filterConfig = \Contao\System::getContainer()->get('huh.filter.registry')->findById($model->pid))) {
					return [];
				}
				
				return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
					[
						'dataContainer' => $filterConfig->getFilter()['dataContainer'],
					]
				);
			},
			'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'multiple' => true, 'mandatory' => true],
			'sql'              => "blob NULL",
		],
		'customOptions'   => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customOptions'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'options'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['options'],
			'exclude'   => true,
			'inputType' => 'optionWizard',
			'eval'      => ['mandatory' => true, 'allowHtml' => true],
			'xlabel'    => [
				['tl_filter_config_element', 'optionImportWizard'],
			],
			'sql'       => "blob NULL",
		],
		'customName'      => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customName'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'name'            => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['name'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['mandatory' => true, 'maxlength' => 128, 'doNotCopy' => true, 'tl_class' => 'clr'],
			'sql'       => "varchar(128) NOT NULL default ''",
		],
		'addPlaceholder'  => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addPlaceholder'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'placeholder'     => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['placeholder'],
			'exclude'          => true,
			'inputType'        => 'select',
			'options_callback' => function (\DataContainer $dc) {
				return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.placeholder');
			},
			'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true],
			'sql'              => "varchar(128) NOT NULL default ''",
		],
		'customLabel'     => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customLabel'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'label'           => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['label'],
			'exclude'          => true,
			'inputType'        => 'select',
			'options_callback' => function (\DataContainer $dc) {
				return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.label');
			},
			'eval'             => ['chosen' => true, 'mandatory' => true, 'maxlength' => 128, 'includeBlankOption' => true],
			'sql'              => "varchar(128) NOT NULL default ''",
		],
		'dateTimeFormat'  => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['dateTimeFormat'],
			'exclude'   => true,
			'inputType' => 'select',
			'options'   => ['datim', 'date', 'time'],
			//			'options_callback' => function (\DataContainer $dc) {
			//				return \Contao\System::getContainer()->get('huh.utils.choice.message')->getCachedChoices('huh.filter.label');
			//			},
			'eval'      => ['mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true],
			'sql'       => "varchar(5) NOT NULL default ''",
		],
		'startField'      => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['startField'],
			'exclude'          => true,
			'filter'           => true,
			'inputType'        => 'select',
			'options_callback' => function (\Contao\DataContainer $dc) {
				if (null === ($model = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
					return [];
				}
				
				if (null === ($filterConfig = \Contao\System::getContainer()->get('huh.filter.registry')->findById($model->pid))) {
					return [];
				}
				
				return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
					[
						'dataContainer' => $filterConfig->getFilter()['dataContainer'],
					]
				);
			},
			'eval'             => ['chosen' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
			'sql'              => "varchar(64) NOT NULL default ''",
		],
		'endField'        => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['endField'],
			'exclude'          => true,
			'filter'           => true,
			'inputType'        => 'select',
			'options_callback' => function (\Contao\DataContainer $dc) {
				if (null === ($model = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
					return [];
				}
				
				if (null === ($filterConfig = \Contao\System::getContainer()->get('huh.filter.registry')->findById($model->pid))) {
					return [];
				}
				
				return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
					[
						'dataContainer' => $filterConfig->getFilter()['dataContainer'],
					]
				);
			},
			'eval'             => ['chosen' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
			'sql'              => "varchar(64) NOT NULL default ''",
		],
		'minDate'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['minDate'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['datepicker' => true, 'rgxp' => 'date', 'tl_class' => 'w50'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'maxDate'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['maxDate'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['datepicker' => true, 'rgxp' => 'date', 'tl_class' => 'w50'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'minTime'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['minTime'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['timepicker' => true, 'rgxp' => 'time', 'tl_class' => 'w50'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'maxTime'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['maxTime'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['timepicker' => true, 'rgxp' => 'time', 'tl_class' => 'w50'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'expanded'        => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['expanded'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['tl_class' => 'w50'],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'multiple'        => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['multiple'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['tl_class' => 'w50'],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'grouping'        => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['grouping'],
			'exclude'   => true,
			'default'   => false,
			'inputType' => 'checkbox',
			'eval'      => ['tl_class' => 'w50 clr'],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'scale'           => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['scale'],
			'exclude'   => true,
			'inputType' => 'text',
			'default'   => 0,
			'eval'      => ['tl_class' => 'clr w50', 'rgxp' => 'natural', 'maxlength' => 2],
			'sql'       => "int(2) unsigned NOT NULL default '0'",
		],
		'roundingMode'    => [
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
		'currency'        => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['currency'],
			'exclude'   => true,
			'inputType' => 'select',
			'default'   => 'EUR',
			'options'   => Symfony\Component\Intl\Intl::getCurrencyBundle()->getCurrencyNames(),
			'eval'      => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'maxlength' => 3],
			'sql'       => "varchar(3) NOT NULL default ''",
		],
		'divisor'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['divisor'],
			'exclude'   => true,
			'inputType' => 'text',
			'default'   => 1,
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 10],
			'sql'       => "int(10) unsigned NOT NULL default '1'",
		],
		'alwaysEmpty'     => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['alwaysEmpty'],
			'exclude'   => true,
			'default'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['tl_class' => 'w50 clr'],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'percentType'     => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['percentType'],
			'inputType' => 'select',
			'exclude'   => true,
			'default'   => 'fractional',
			'options'   => ['fractional', 'integer'],
			'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference']['percentType'],
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 10],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'defaultProtocol' => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultProtocol'],
			'exclude'   => true,
			'inputType' => 'text',
			'default'   => 'http',
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 12],
			'sql'       => "varchar(12) NOT NULL default ''",
		],
		'min'             => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['min'],
			'exclude'   => true,
			'default'   => '12:00',
			'inputType' => 'text',
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 12, 'mandatory' => true],
			'sql'       => "varchar(12) NOT NULL default ''",
		],
		'max'             => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['max'],
			'exclude'   => true,
			'inputType' => 'text',
			'default'   => '14:00',
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 12, 'mandatory' => true],
			'sql'       => "varchar(12) NOT NULL default ''",
		],
		'step'            => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['step'],
			'exclude'   => true,
			'default'   => '00:15',
			'inputType' => 'text',
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 12, 'mandatory' => true],
			'sql'       => "varchar(12) NOT NULL default ''",
		],
		'customCountries' => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customCountries'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true, 'doNotCopy' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'countries'       => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['countries'],
			'exclude'   => true,
			'inputType' => 'select',
			'default'   => 'EUR',
			'options'   => Symfony\Component\Intl\Intl::getRegionBundle()->getCountryNames(),
			'eval'      => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'multiple' => true, 'mandatory' => true],
			'sql'       => "blob NULL",
		],
		'customLanguages' => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customLanguages'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'languages'       => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['languages'],
			'exclude'   => true,
			'inputType' => 'select',
			'default'   => 'EUR',
			'options'   => Symfony\Component\Intl\Intl::getLanguageBundle()->getLanguageNames(),
			'eval'      => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'multiple' => true, 'mandatory' => true],
			'sql'       => "blob NULL",
		],
		'customLocales'   => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customLocales'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'locales'         => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['locales'],
			'exclude'   => true,
			'inputType' => 'select',
			'default'   => 'EUR',
			'options'   => Symfony\Component\Intl\Intl::getLocaleBundle()->getLocaleNames(),
			'eval'      => ['tl_class' => 'clr w50 wizard', 'chosen' => 'true', 'multiple' => true, 'mandatory' => true],
			'sql'       => "blob NULL",
		],
		'customValue'     => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['customValue'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => ['submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'value'           => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['value'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['tl_class' => 'clr w50 wizard', 'mandatory' => true],
			'sql'       => "varchar(255) NOT NULL default ''",
		],
		'parents'         => [
			'label'            => &$GLOBALS['TL_LANG']['tl_filter_config_element']['parents'],
			'default'          => 'text',
			'exclude'          => true,
			'inputType'        => 'checkboxWizard',
			'options_callback' => ['huh.filter.choice.parent', 'getChoices'],
			'eval'             => ['tl_class' => 'wizard', 'multiple' => true],
			'sql'              => "blob NULL",
		],
		'cssClass'        => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['cssClass'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['tl_class' => 'w50', 'maxlength' => 64],
			'sql'       => "varchar(64) NOT NULL default ''",
		],
		'published'       => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['published'],
			'exclude'   => true,
			'filter'    => true,
			'inputType' => 'checkbox',
			'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
			'sql'       => "char(1) NOT NULL default ''",
		],
		'start'           => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['start'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
		'stop'            => [
			'label'     => &$GLOBALS['TL_LANG']['tl_filter_config_element']['stop'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
			'sql'       => "varchar(10) NOT NULL default ''",
		],
	],
];


class tl_filter_config_element extends \Backend
{
	
	public function listElements($arrRow)
	{
		return '<div class="tl_content_left">' . ($arrRow['title'] ?: $arrRow['id']) . ' <span style="color:#b3b3b3; padding-left:3px">['
			   . $GLOBALS['TL_LANG']['tl_filter_config_element']['reference']['type'][$arrRow['type']] . ']</span></div>';
	}
	
	public function checkPermission()
	{
		$user     = \BackendUser::getInstance();
		$database = \Database::getInstance();
		
		if ($user->isAdmin) {
			return;
		}
		
		// Set the root IDs
		if (!is_array($user->filters) || empty($user->filters)) {
			$root = [0];
		} else {
			$root = $user->filters;
		}
		
		$id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;
		
		// Check current action
		switch (\Input::get('act')) {
			case 'paste':
				// Allow
				break;
			
			case 'create':
				if (!strlen(\Input::get('pid')) || !in_array(\Input::get('pid'), $root)) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException(
						'Not enough permissions to create filter_element items in filter_element archive ID ' . \Input::get('pid') . '.'
					);
				}
				break;
			
			case 'cut':
			case 'copy':
				if (!in_array(\Input::get('pid'), $root)) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException(
						'Not enough permissions to ' . \Input::get('act') . ' filter_element item ID ' . $id . ' to filter_element archive ID '
						. \Input::get('pid') . '.'
					);
				}
			// NO BREAK STATEMENT HERE
			
			case 'edit':
			case 'show':
			case 'delete':
			case 'toggle':
			case 'feature':
				$objArchive = $database->prepare("SELECT pid FROM tl_filter_config_element WHERE id=?")->limit(1)->execute($id);
				
				if ($objArchive->numRows < 1) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid filter_element item ID ' . $id . '.');
				}
				
				if (!in_array($objArchive->pid, $root)) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException(
						'Not enough permissions to ' . \Input::get('act') . ' filter_element item ID ' . $id . ' of filter_element archive ID '
						. $objArchive->pid . '.'
					);
				}
				break;
			
			case 'select':
			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
			case 'cutAll':
			case 'copyAll':
				if (!in_array($id, $root)) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException(
						'Not enough permissions to access filter_element archive ID ' . $id . '.'
					);
				}
				
				$objArchive = $database->prepare("SELECT id FROM tl_filter_config_element WHERE pid=?")->execute($id);
				
				if ($objArchive->numRows < 1) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid filter_element archive ID ' . $id . '.');
				}
				
				/** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
				$session = \System::getContainer()->get('session');
				
				$session                   = $session->all();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
				$session->replace($session);
				break;
			
			default:
				if (strlen(\Input::get('act'))) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Input::get('act') . '".');
				} elseif (!in_array($id, $root)) {
					throw new \Contao\CoreBundle\Exception\AccessDeniedException(
						'Not enough permissions to access filter_element archive ID ' . $id . '.'
					);
				}
				break;
		}
	}
	
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		$user = \BackendUser::getInstance();
		
		if (strlen(\Input::get('tid'))) {
			$this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}
		
		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$user->hasAccess('tl_filter_config_element::published', 'alexf')) {
			return '';
		}
		
		$href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);
		
		if (!$row['published']) {
			$icon = 'invisible.svg';
		}
		
		return '<a href="' . $this->addToUrl($href) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>' . \Image::getHtml(
				$icon,
				$label,
				'data-state="' . ($row['published'] ? 1 : 0) . '"'
			) . '</a> ';
	}
	
	public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
	{
		$user     = \BackendUser::getInstance();
		$database = \Database::getInstance();
		
		// Set the ID and action
		\Input::setGet('id', $intId);
		\Input::setGet('act', 'toggle');
		
		if ($dc) {
			$dc->id = $intId; // see #8043
		}
		
		// Trigger the onload_callback
		if (is_array($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onload_callback'])) {
			foreach ($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onload_callback'] as $callback) {
				if (is_array($callback)) {
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($dc);
				} elseif (is_callable($callback)) {
					$callback($dc);
				}
			}
		}
		
		// Check the field access
		if (!$user->hasAccess('tl_filter_config_element::published', 'alexf')) {
			throw new \Contao\CoreBundle\Exception\AccessDeniedException(
				'Not enough permissions to publish/unpublish filter_element item ID ' . $intId . '.'
			);
		}
		
		// Set the current record
		if ($dc) {
			$objRow = $database->prepare("SELECT * FROM tl_filter_config_element WHERE id=?")->limit(1)->execute($intId);
			
			if ($objRow->numRows) {
				$dc->activeRecord = $objRow;
			}
		}
		
		$objVersions = new \Versions('tl_filter_config_element', $intId);
		$objVersions->initialize();
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_filter_config_element']['fields']['published']['save_callback'])) {
			foreach ($GLOBALS['TL_DCA']['tl_filter_config_element']['fields']['published']['save_callback'] as $callback) {
				if (is_array($callback)) {
					$this->import($callback[0]);
					$blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
				} elseif (is_callable($callback)) {
					$blnVisible = $callback($blnVisible, $dc);
				}
			}
		}
		
		$time = time();
		
		// Update the database
		$database->prepare("UPDATE tl_filter_config_element SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute(
			$intId
		);
		
		if ($dc) {
			$dc->activeRecord->tstamp    = $time;
			$dc->activeRecord->published = ($blnVisible ? '1' : '');
		}
		
		// Trigger the onsubmit_callback
		if (is_array($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onsubmit_callback'])) {
			foreach ($GLOBALS['TL_DCA']['tl_filter_config_element']['config']['onsubmit_callback'] as $callback) {
				if (is_array($callback)) {
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($dc);
				} elseif (is_callable($callback)) {
					$callback($dc);
				}
			}
		}
		
		$objVersions->create();
	}
	
	/**
	 * Add a link to the option items import wizard
	 *
	 * @return string
	 */
	public function optionImportWizard()
	{
		return ' <a href="' . $this->addToUrl('key=option') . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['ow_import'][1])
			   . '" onclick="Backend.getScrollOffset()">' . Image::getHtml('tablewizard.gif', $GLOBALS['TL_LANG']['MSC']['ow_import'][0]) . '</a>';
	}
}
