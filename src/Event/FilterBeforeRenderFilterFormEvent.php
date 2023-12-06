<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use Symfony\Contracts\EventDispatcher\Event;

class FilterBeforeRenderFilterFormEvent extends Event
{
    /** @var string */
    private $template;

    /** @var array */
    private $context;
    /**
     * @var FilterConfig
     */
    private $filterConfig;

    public function __construct(string $template, array $context, FilterConfig $filterConfig)
    {
        $this->template = $template;
        $this->context = $context;
        $this->filterConfig = $filterConfig;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getFilterConfig(): FilterConfig
    {
        return $this->filterConfig;
    }
}
