<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Type;

class FilterTypeCollection
{
    /**
     * @var iterable|FilterTypeInterface[]
     */
    protected $typeIterable;

    /**
     * @var FilterTypeInterface[]
     */
    protected $types;

    public function __construct(iterable $typeIterable)
    {
        $this->typeIterable = $typeIterable;
    }

    public function getTypes(): array
    {
        if (!$this->types) {
            $this->types = [];

            foreach ($this->typeIterable as $type) {
                $this->types[$type::getType()] = $type;
            }
        }

        return $this->types;
    }

    public function getInitialTypes(): array
    {
        if (!$this->types) {
            $this->types = [];

            foreach ($this->typeIterable as $type) {
                $this->types[$type::getType()] = $type;
            }
        }

        return $this->types;
    }

    public function hasType(string $type): bool
    {
        return isset($this->getTypes()[$type]);
    }

    public function getType(string $type): ?FilterTypeInterface
    {
        if ($this->hasType($type)) {
            return $this->getTypes()[$type];
        }

        return null;
    }
}
