<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;

class ButtonType extends AbstractFilterType
{
    const TYPE = 'future_button';

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

    public function getPalette(): string
    {
        // TODO: Implement getPalette() method.
    }
}
