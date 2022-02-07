<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\EventDispatcher\Event;

class ModifyJsonResponseEvent extends Event
{
    const NAME = 'huh.filter.event.modify_json_response_event';

    /**
     * @var JsonResponse
     */
    protected $response;

    /**
     * @var FilterConfig
     */
    protected $filter;

    public function __construct(JsonResponse $response, FilterConfig $filter)
    {
        $this->response = $response;
        $this->filter = $filter;
    }

    public function getResponse(): JsonResponse
    {
        return $this->response;
    }

    public function setResponse(JsonResponse $response): void
    {
        $this->response = $response;
    }

    public function getFilter(): FilterConfig
    {
        return $this->filter;
    }

    public function setFilter(FilterConfig $filter): void
    {
        $this->filter = $filter;
    }
}
