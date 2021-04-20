<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterQuery;

use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;

class FilterQueryPart
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $query;

    /**
     * @var string
     */
    public $wildcard;

    /**
     * @var string|int|array|\DateTime
     */
    public $value;

    /**
     * @var int|string|null
     */
    public $valueType;
    /**
     * @var int
     */
    protected $filterElementId;

    public function __construct(FilterTypeContext $context)
    {
        $this->name = $context->getName();
        $this->filterElementId = $context->getId();
    }

    public function getWildcard(): string
    {
        return $this->wildcard;
    }

    public function setWildcard(string $wildcard): void
    {
        $this->wildcard = $wildcard;
    }

    /**
     * @return array|\DateTime|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|\DateTime|int|string $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getValueType()
    {
        return $this->valueType;
    }

    public function setValueType($valueType): void
    {
        $this->valueType = $valueType;
    }
}
