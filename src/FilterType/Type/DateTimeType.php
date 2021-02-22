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
    const TYPE = 'future_date_time';

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery($filterTypeContext): string
    {
        // TODO: Implement buildQuery() method.
    }

    public function buildForm($filterTypeContext)
    {
        // TODO: Implement buildForm() method.
    }

    public function getPalette(FilterTypeContext $context): string
    {
        return parent::getPalette($context);
    }
}
