<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

interface FilterTypeInterface
{
    public function buildQuery(FilterTypeContext $filterTypeContext);

    public function buildForm(FilterTypeContext $filterTypeContext);

    public function getPalette(string $prependPalette, string $appendPalette): string;

    public function getOperators(): array;
}
