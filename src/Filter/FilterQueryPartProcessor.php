<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

class FilterQueryPartProcessor
{
    /**
     * @var FilterQueryPart[]
     */
    private array $parts = [];

    /**
     * @return FilterQueryPart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param FilterQueryPart[] $parts
     */
    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }

    public function addPart(FilterQueryPart $part): void
    {
        $this->parts[$part->name] = $part;
    }

    public function removePart(string $name): void
    {
        unset($name, $this->parts);
    }
}
