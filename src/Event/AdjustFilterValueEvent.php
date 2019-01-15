<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\EventDispatcher\Event;

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
     * @param array                    $data
     * @param FilterConfigElementModel $element
     * @param string                   $name
     * @param FilterConfig             $config
     * @param array                    $dca
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

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return FilterConfigElementModel
     */
    public function getElement(): FilterConfigElementModel
    {
        return $this->element;
    }

    /**
     * @param FilterConfigElementModel $element
     */
    public function setElement(FilterConfigElementModel $element): void
    {
        $this->element = $element;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return FilterConfig
     */
    public function getConfig(): FilterConfig
    {
        return $this->config;
    }

    /**
     * @param FilterConfig $config
     */
    public function setConfig(FilterConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getDca(): array
    {
        return $this->dca;
    }

    /**
     * @param array $dca
     */
    public function setDca(array $dca): void
    {
        $this->dca = $dca;
    }
}
