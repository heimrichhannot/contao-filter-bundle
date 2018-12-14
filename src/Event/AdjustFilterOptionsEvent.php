<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormBuilderInterface;

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
     * @param string                   $name
     * @param array                    $options
     * @param FilterConfigElementModel $element
     * @param FormBuilderInterface     $builder
     * @param array                    $data
     */
    public function __construct(string $name, array $options, FilterConfigElementModel $element, FormBuilderInterface $builder, FilterConfig $config)
    {
        $this->name = $name;
        $this->options = $options;
        $this->element = $element;
        $this->builder = $builder;
        $this->config = $config;
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
     * @return FormBuilderInterface
     */
    public function getBuilder(): FormBuilderInterface
    {
        return $this->builder;
    }

    /**
     * @param FormBuilderInterface $builder
     */
    public function setBuilder(FormBuilderInterface $builder): void
    {
        $this->builder = $builder;
    }
}
