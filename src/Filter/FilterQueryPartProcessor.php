<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class FilterQueryPartProcessor
{
    /**
     * @var DatabaseUtil
     */
    protected $databaseUtil;

    public function __construct(DatabaseUtil $databaseUtil)
    {
        $this->databaseUtil = $databaseUtil;
    }

    public function composeQueryPart(FilterTypeContext $context): FilterQueryPart
    {
        return new FilterQueryPart($context, $this->databaseUtil);
    }
}
