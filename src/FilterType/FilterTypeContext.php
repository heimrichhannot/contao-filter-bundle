<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

class FilterTypeContext implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $name = '';
    /**
     * @var string
     */
    private $defaultValue = '';
    /**
     * @var string
     */
    private $value = '';

    /**
     * @var bool
     */
    private $initial = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getContext(): self
    {
        return $this;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this);
    }

    public function isInitial(): bool
    {
        return $this->initial;
    }

    public function setInitial(): void
    {
        $this->initial = true;
    }
}
