<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Type;

interface InitialFilterTypeInterface
{
    public function getInitialPalette(string $prependPalette, string $appendPalette): string;

    public function getInitialValueTypes(array $types): array;
}
