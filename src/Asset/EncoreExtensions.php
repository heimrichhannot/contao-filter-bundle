<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;

class EncoreExtensions implements \HeimrichHannot\EncoreContracts\EncoreExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBundle(): string
    {
        return HeimrichHannotContaFilterBundle::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-filter-bundle', 'src/Resources/assets/js/contao-filter-bundle.js')
                ->setRequiresCss(false)
                ->addJsEntryToRemoveFromGlobals('contao-filter-bundle'),
        ];
    }
}
