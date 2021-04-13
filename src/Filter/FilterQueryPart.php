<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;

class FilterQueryPart
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $query;
    /**
     * @var DatabaseUtil
     */
    protected $databaseUtil;

    public function __construct(FilterTypeContext $context, DatabaseUtil $databaseUtil)
    {
        $this->databaseUtil = $databaseUtil;
        $this->name = $context->getName();
        $this->query = $this->composeQuery($context);
    }

    private function composeQuery(FilterTypeContext $context): string
    {
        return $this->databaseUtil->composeWhereForQueryBuilder(
            $context->getQueryBuilder(),
            $context->getField(),
            $context->getOperator(),
            null,
            $context->getValue(),
            ['wildcardSuffix' => $context->getId()]
        );
    }
}
