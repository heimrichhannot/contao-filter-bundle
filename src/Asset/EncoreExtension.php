<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;

class EncoreExtension implements EncoreExtensionInterface
{
    public function getBundle(): string
    {
        return HeimrichHannotContaoFilterBundle::class;
    }

    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-filter-bundle', 'src/Resources/assets/js/contao-filter-bundle.js')
                ->addJsEntryToRemoveFromGlobals('contao-filter-bundle'),
        ];
    }
}
