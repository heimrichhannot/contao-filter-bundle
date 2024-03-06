<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use Contao\Model\Collection;
use Symfony\Contracts\EventDispatcher\Event;

class FilterConfigInitEvent extends Event
{
    /**
     * @var array
     */
    private $filter;
    /**
     * @var string
     */
    private $sessionKey;
    /**
     * @var Collection|null
     */
    private $elements;

    public function __construct(array $filter, string $sessionKey, Collection $elements = null)
    {
        $this->filter = $filter;
        $this->sessionKey = $sessionKey;
        $this->elements = $elements;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    public function getElements(): ?Collection
    {
        return $this->elements;
    }

    public function setElements(?Collection $elements): void
    {
        $this->elements = $elements;
    }
}
