<?php
/**
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;


class FilterQueryPartCollection
{
    /**
     * @var FilterQueryPart[]
     */
    private $parts = [];

    public function getParts(): array
    {
        return $this->parts;
    }

    public function addPart(FilterQueryPart $part): void
    {
        $this->parts[$part->name] = $part;
    }

    public function removePartByName(string $name): void
    {
        unset($this->parts[$name]);
    }

}