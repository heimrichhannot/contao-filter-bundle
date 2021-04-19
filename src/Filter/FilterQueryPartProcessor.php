<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;

class FilterQueryPartProcessor
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function composeQueryPart(FilterTypeContext $context): FilterQueryPart
    {
        return new FilterQueryPart($context, $this->connection);
    }
}
