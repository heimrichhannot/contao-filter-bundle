<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use Symfony\Component\EventDispatcher\Event;

class ModifyFilterQueryPartsEvent extends Event
{
    public const NAME = 'huh.filter.modify_filter_query_parts_event';

    /**
     * @var FilterQueryPartCollection
     */
    protected $partsCollection;

    /**
     * @var array|null
     */
    protected $filter;

    public function __construct(FilterQueryPartCollection $partsCollection, ?array $filter)
    {
        $this->partsCollection = $partsCollection;
        $this->filter = $filter;
    }

    public function getPartsCollection(): FilterQueryPartCollection
    {
        return $this->partsCollection;
    }

    public function setPartsCollection(FilterQueryPartCollection $partsCollection): void
    {
        $this->partsCollection = $partsCollection;
    }

    public function getFilter(): ?array
    {
        return $this->filter;
    }

    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }
}
