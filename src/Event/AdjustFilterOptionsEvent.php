<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AdjustFilterOptionsEvent extends Event
{
    const NAME = 'huh.filter.event.adjust_filter_options_event';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var FilterConfigElementModel
     */
    protected $element;

    /**
     * @var FormBuilderInterface
     */
    protected $builder;

    /**
     * @var FilterConfig
     */
    protected $config;

    /**
     * AdjustFilterOptionsEvent constructor.
     *
     * @param array $data
     */
    public function __construct(string $name, array $options, FilterConfigElementModel $element, FormBuilderInterface $builder, FilterConfig $config)
    {
        $this->name = $name;
        $this->options = $options;
        $this->element = $element;
        $this->builder = $builder;
        $this->config = $config;
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

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getBuilder(): FormBuilderInterface
    {
        return $this->builder;
    }

    public function setBuilder(FormBuilderInterface $builder): void
    {
        $this->builder = $builder;
    }
}
