<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Contracts\EventDispatcher\Event;

class AdjustFilterValueEvent extends Event
{
    const NAME = 'huh.filter.event.adjust_filter_value_event';

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var FilterConfigElementModel
     */
    protected $element;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var FilterConfig
     */
    protected $config;

    /**
     * @var array
     */
    protected $dca;

    /**
     * @param $value
     */
    public function __construct($value, array $data, FilterConfigElementModel $element, string $name, FilterConfig $config, array $dca)
    {
        $this->value = $value;
        $this->data = $data;
        $this->element = $element;
        $this->name = $name;
        $this->config = $config;
        $this->dca = $dca;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getElement(): FilterConfigElementModel
    {
        return $this->element;
    }

    public function setElement(FilterConfigElementModel $element): void
    {
        $this->element = $element;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConfig(): FilterConfig
    {
        return $this->config;
    }

    public function setConfig(FilterConfig $config): void
    {
        $this->config = $config;
    }

    public function getDca(): array
    {
        return $this->dca;
    }

    public function setDca(array $dca): void
    {
        $this->dca = $dca;
    }
}
