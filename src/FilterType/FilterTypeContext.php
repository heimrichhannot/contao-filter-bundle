<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

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
    private $parent = null;

    /**
     * @var string|array|int|DateTimeInterface
     */
    private $value;

    /**
     * @var string|int
     */
    private $valueType;

    /**
     * @return array|string|int|DateTimeInterface
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|array|int|DateTimeInterface $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return Model
     */
    public function getParent(): ?Model
    {
        return $this->parent;
    }

    public function setParent(?Model $parent): void
    {
        $this->parent = $parent;
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
