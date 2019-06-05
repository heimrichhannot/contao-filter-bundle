<?php


namespace HeimrichHannot\FilterBundle\Event;


use HeimrichHannot\FilterBundle\Config\FilterConfig;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $this->filter   = $filter;
    }

    /**
     * @return JsonResponse
     */
    public function getResponse(): JsonResponse
    {
        return $this->response;
    }

    /**
     * @param JsonResponse $response
     */
    public function setResponse(JsonResponse $response): void
    {
        $this->response = $response;
    }

    /**
     * @return FilterConfig
     */
    public function getFilter(): FilterConfig
    {
        return $this->filter;
    }

    /**
     * @param FilterConfig $filter
     */
    public function setFilter(FilterConfig $filter): void
    {
        $this->filter = $filter;
    }
}