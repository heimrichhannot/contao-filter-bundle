<?php

namespace HeimrichHannot\FilterBundle\Event;

use Contao\Model\Collection;
use Symfony\Component\EventDispatcher\Event;

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

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @param string $sessionKey
     */
    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    /**
     * @return Collection|null
     */
    public function getElements(): ?Collection
    {
        return $this->elements;
    }

    /**
     * @param Collection|null $elements
     */
    public function setElements(?Collection $elements): void
    {
        $this->elements = $elements;
    }


}