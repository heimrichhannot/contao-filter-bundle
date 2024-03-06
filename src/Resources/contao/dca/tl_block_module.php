<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\Blocks\Model\BlockModuleModel;
use HeimrichHannot\Blocks\BlockModuleModel as LegacyBlockModuleModel;

if (class_exists(BlockModuleModel::class) || class_exists(LegacyBlockModuleModel::class))
{
    $dca = &$GLOBALS['TL_DCA']['tl_block_module'];

    $dca['palettes']['__selector__'][] = 'useFilter';

    $dca['palettes']['default'] = str_replace('keywordPages', 'keywordPages,useFilter', $dca['palettes']['default']);
    $dca['palettes']['article'] = str_replace('keywordPages', 'keywordPages,useFilter', $dca['palettes']['article']);
    $dca['palettes']['content'] = str_replace('keywordPages', 'keywordPages,useFilter', $dca['palettes']['content']);

    $dca['subpalettes']['useFilter'] = 'filter,filterKeywords';

    $dca['fields']['useFilter'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_block_module']['useFilter'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'clr', 'submitOnChange' => true],
        'sql' => "char(1) NOT NULL default ''",
    ];

    $dca['fields']['filter'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_block_module']['filter'],
        'exclude' => true,
        'inputType' => 'select',
        'foreignKey' => 'tl_filter_config.title',
        'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'clr'],
        'sql' => "char(64) NOT NULL default ''",
    ];

    $dca['fields']['filterKeywords'] = [
        'label' => &$GLOBALS['TL_LANG']['tl_block_module']['filterKeywords'],
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
        'sql' => "char(128) NOT NULL default ''",
    ];
}
