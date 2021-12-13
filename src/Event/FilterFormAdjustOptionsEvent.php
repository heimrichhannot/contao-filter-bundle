<?php

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use Symfony\Component\EventDispatcher\Event;

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

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @return FilterConfig
     */
    public function getFilterConfig(): FilterConfig
    {
        return $this->filterConfig;
    }
}