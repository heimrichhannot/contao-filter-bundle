<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterQuery;

class FilterQueryPartCollection
{
    /**
     * @var FilterQueryPart[]
     */
    private $parts = [];

    /**
     * @var array
     */
    private $targetFields = [];

    public function getParts(): array
    {
        return $this->parts;
    }

    public function getPartByName(string $name): ?FilterQueryPart
    {
        if (!\array_key_exists($name, $this->parts)) {
            return null;
        }

        return $this->parts[$name];
    }

    public function addPart(FilterQueryPart $part): void
    {
        $this->parts[$part->getName()] = $part;
        $this->addTargetField($part->getField(), $part->getName(), $part->isInitial(), $part->isOverridable());
    }

    public function removePartByName(string $name): void
    {
        unset($this->parts[$name]);
    }

    public function addTargetField(string $field, string $partName, bool $isInitial, bool $overridable): void
    {
        $this->targetFields[$field][$partName] = ['initial' => $isInitial, 'overridable' => $overridable];
    }

    public function removeTargetField(string $field = '', string $partName = ''): void
    {
        if ('' === $field) {
            return;
        }

        unset($this->targetFields[$field][$partName]);

        if (empty($this->targetFields[$field])) {
            unset($this->targetFields[$field]);
        }
    }

    public function getTargetFields(): array
    {
        return $this->targetFields;
    }

    public function reset(): void
    {
        $this->parts = [];
        $this->targetFields = [];
    }
}
