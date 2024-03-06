<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use Symfony\Contracts\EventDispatcher\Event;

class FilterFormAdjustOptionsEvent extends Event
{
    /**
     * @var array
     */
    private $options;
    /**
     * @var array
     */
    private $filter;
    /**
     * @var FilterConfig
     */
    private $filterConfig;

    public function __construct(array $options, array $filter, FilterConfig $filterConfig)
    {
        $this->options = $options;
        $this->filter = $filter;
        $this->filterConfig = $filterConfig;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function getFilterConfig(): FilterConfig
    {
        return $this->filterConfig;
    }
}
