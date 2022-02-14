<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

\Controller::loadLanguageFile('tl_fieldpalette');
\Controller::loadDataContainer('tl_fieldpalette');

$GLOBALS['TL_DCA']['tl_filter_preselect'] = $GLOBALS['TL_DCA']['tl_fieldpalette'];
$dc = &$GLOBALS['TL_DCA']['tl_filter_preselect'];

/*
 * Config
 */

$dc['config']['onload_callback'][] = ['huh.filter.backend.filter_preselect', 'prepareChoiceTypes'];

/*
 * List
 */
$dc['list']['label']['label_callback'] = ['huh.filter.backend.filter_preselect', 'adjustLabel'];

/*
 * Palettes
 */
$dc['palettes']['__selector__'][] = 'initialValueType';

/*
 * Subpalettes
 */
$dc['subpalettes']['initialValueType_'.\HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_SCALAR] = 'initialValue';
$dc['subpalettes']['initialValueType_'.\HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_ARRAY] = 'initialValueArray';
$dc['subpalettes']['initialValueType_'.\HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPE_LATEST] = 'parentField';

/**
 * Fields.
 */
$fields = [
    'element' => [
        'label' => &$GLOBALS['TL_LANG']['tl_filter_preselect']['element'],
        'inputType' => 'select',
        'options_callback' => function (Contao\DataContainer $dc) {
            if (null === ($content = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->activeRecord->ptable, $dc->activeRecord->pid))) {
                return [];
            }

            if (null === ($config = System::getContainer()->get('huh.filter.manager')->findById($content->filterConfig))) {
                return [];
            }

            $choices = [];

            foreach ($config->getElements() as $element) {
                $choices[$element->id] = sprintf('%s [ID: %s]', $element->title, $element->id);
            }

            return $choices;
        },
        'eval' => ['submitOnChange' => true, 'mandatory' => true],
        'sql' => "int(10) NOT NULL default '0'",
    ],
    'initialValueType' => [
        'label' => &$GLOBALS['TL_LANG']['tl_filter_preselect']['initialValueType'],
        'exclude' => true,
        'filter' => true,
        'inputType' => 'select',
        'options_callback' => ['huh.filter.listener.dca.callback.filterconfigelement', 'getValueTypeOptions'],
        'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference'],
        'eval' => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
        'sql' => "varchar(16) NOT NULL default ''",
    ],
    'initialValue' => [
        'label' => &$GLOBALS['TL_LANG']['tl_filter_preselect']['initialValue'],
        'exclude' => true,
        'search' => true,
        'inputType' => 'text',
        'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
        'sql' => "varchar(128) NOT NULL default ''",
    ],
    'initialValueArray' => [
        'label' => &$GLOBALS['TL_LANG']['tl_filter_preselect']['initialValue'],
        'inputType' => 'multiColumnEditor',
        'eval' => [
            'tl_class' => 'long clr',
            'multiColumnEditor' => [
                'fields' => [
                    'value' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_filter_preselect']['initialValue_value'],
                        'exclude' => true,
                        'search' => true,
                        'inputType' => 'text',
                        'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'groupStyle' => 'width: 200px'],
                    ],
                ],
            ],
        ],
        'sql' => 'blob NULL',
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);
