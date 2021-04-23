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
    private $field;

    /**
     * @var int
     */
    private $filterElementId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string|int|array|\DateTime
     */
    private $value;

    /**
     * @var int|string|null
     */
    private $valueType;

    /**
     * @var string
     */
    private $wildcard;

    public function __construct(FilterTypeContext $filterTypeContext)
    {
        $elementConfig = $filterTypeContext->getElementConfig();

        $this->name = $elementConfig->getElementName();
        $this->filterElementId = $elementConfig->id;
        $this->operator = $elementConfig->operator;
        $this->field = $elementConfig->field;
        $this->value = $filterTypeContext->getValue();
        $this->valueType = $filterTypeContext->getValueType();
        $this->wildcard = ':'.str_replace('.', '_', $elementConfig->field).'_'.$elementConfig->id;
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

    /**
     * @return int|string|null
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * @param $valueType
     */
    public function setValueType($valueType): void
    {
        $this->valueType = $valueType;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getFilterElementId(): int
    {
        return $this->filterElementId;
    }

    public function setFilterElementId(int $filterElementId): void
    {
        $this->filterElementId = $filterElementId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }
}
