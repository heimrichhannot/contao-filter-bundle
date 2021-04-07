<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;

class DateTimeType extends AbstractFilterType
{
    const TYPE = 'date_time_type';

    public static function test(): string
    {
        return 'test';
    }

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext): string
    {
        // TODO: Implement buildQuery() method.
    }

    public function buildForm($filterTypeContext)
    {
        // TODO: Implement buildForm() method.
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return parent::getPalette($prependPalette, $appendPalette);
    }
}
