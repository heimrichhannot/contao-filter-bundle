<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;

class FilterQueryPartProcessor
{
    public function composeQueryPart(FilterTypeContext $context): FilterQueryPart
    {
        return new FilterQueryPart($context);
    }
}
