<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Type;

use Contao\Model;
use DateTimeInterface;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\Form\FormBuilderInterface;

class FilterTypeContext
{
    /**
     * @var FilterConfigElementModel
     */
    private $elementConfig;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var Model
     */
    private $filterConfig = null;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string|int
     */
    private $valueType;

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
     * @return Model
     */
    public function getFilterConfig(): ?Model
    {
        return $this->filterConfig;
    }

    public function setFilterConfig(?Model $filterConfig): void
    {
        $this->filterConfig = $filterConfig;
    }

    public function getFormBuilder(): FormBuilderInterface
    {
        return $this->formBuilder;
    }

    public function setFormBuilder(FormBuilderInterface $formBuilder): void
    {
        $this->formBuilder = $formBuilder;
    }

    public function getElementConfig(): FilterConfigElementModel
    {
        return $this->elementConfig;
    }

    public function setElementConfig(FilterConfigElementModel $elementConfig): void
    {
        $this->elementConfig = $elementConfig;
    }

    /**
     * @return int|string
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * @param int|string $valueType
     */
    public function setValueType($valueType): void
    {
        $this->valueType = $valueType;
    }
}
