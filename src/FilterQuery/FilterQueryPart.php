<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterQuery;

use Contao\StringUtil;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;

class FilterQueryPart
{
    /**
     * @var bool
     */
    private $disabled = false;

    /**
     * @var string
     */
    private $field;

    /**
     * @var int
     */
    private $filterElementId;

    /**
     * @var bool
     */
    private $initial = false;

    /**
     * @var mixed
     */
    private $initialValue;

    /**
     * @var string
     */
    private $initialValueType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var bool
     */
    private $overridable = true;

    /**
     * @var string|int|array|\DateTimeInterface
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
        $this->field = $filterTypeContext->getFilterConfig()->row()['dataContainer'].'.'.$elementConfig->field;
        $this->wildcard = ':'.str_replace('.', '_', $elementConfig->field).'_'.$elementConfig->id;

        if ($elementConfig->isInitial) {
            $this->initial = $elementConfig->isInitial;
            $this->initialValue = $elementConfig->initialValue ?: array_column(StringUtil::deserialize($elementConfig->initialValueArray, true), 'value');
            $this->initialValueType = $elementConfig->initialValueType;
            $this->value = $elementConfig->initialValue ?: $this->initialValue;
            $this->valueType = $elementConfig->initialValueType;
            $this->overridable = $elementConfig->isInitialOverridable;
        } else {
            $this->value = $filterTypeContext->getValue();
            $this->valueType = $filterTypeContext->getValueType();
        }
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
     * @return array|\DateTimeInterface|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|\DateTimeInterface|int|string $value
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

    public function isInitial(): bool
    {
        return $this->initial;
    }

    public function setInitial(bool $initial): void
    {
        $this->initial = $initial;
    }

    /**
     * @return mixed
     */
    public function getInitialValue()
    {
        return $this->initialValue;
    }

    /**
     * @param mixed $initialValue
     */
    public function setInitialValue($initialValue): void
    {
        $this->initialValue = $initialValue;
    }

    public function getInitialValueType(): string
    {
        return $this->initialValueType;
    }

    public function setInitialValueType(string $initialValueType): void
    {
        $this->initialValueType = $initialValueType;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function isOverridable(): bool
    {
        return $this->overridable;
    }

    public function setOverridable(bool $overridable): void
    {
        $this->overridable = $overridable;
    }
}
