<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

use Contao\Model;
use Symfony\Component\Form\FormBuilderInterface;

class FilterTypeContext implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $dateTimeFormat;

    /**
     * @var string|array
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $expanded = false;
    /**
     * @var string
     */
    private $field = '';

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $initial = false;

    /**
     * @var string
     */
    private $label = '';

    /**
     * @var bool
     */
    private $isLabelHidden = false;

    /**
     * @var bool
     */
    private $isMultiple = false;

    /**
     * @var string
     */
    private $maxDateTime;

    /**
     * @var string
     */
    private $minDateTime;

    /**
     * @var string
     */
    private $name = '';
    /**
     * @var string
     */
    private $operator = '';
    /**
     * @var Model
     */
    private $parent = null;

    /**
     * string.
     */
    private $placeholder = null;

    /**
     * @var bool
     */
    private $submitOnChange = false;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string|array|int
     */
    private $value;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array|string|int
     */
    public function getValue()
    {
        if (empty($this->value)) {
            return $this->getDefaultValue();
        }

        return $this->value;
    }

    /**
     * @param string|array|int $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getContext(): self
    {
        return $this;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this);
    }

    public function isInitial(): bool
    {
        return $this->initial;
    }

    public function setInitial(): void
    {
        $this->initial = true;
    }

    /**
     * @return Model
     */
    public function getParent(): ?Model
    {
        return $this->parent;
    }

    /**
     * @param Model $parent
     */
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

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return null
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param null $placeholder
     */
    public function setPlaceholder($placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function isLabelHidden(): bool
    {
        return $this->isLabelHidden;
    }

    public function hideLabel(): void
    {
        $this->isLabelHidden = true;
    }

    public function isSubmitOnChange(): bool
    {
        return $this->submitOnChange;
    }

    public function setSubmitOnChange(bool $submitOnChange): void
    {
        $this->submitOnChange = $submitOnChange;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    public function setMultiple(bool $isMultiple): void
    {
        $this->isMultiple = $isMultiple;
    }

    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    public function setExpanded(bool $expanded): void
    {
        $this->expanded = $expanded;
    }

    public function getDateTimeFormat(): string
    {
        return $this->dateTimeFormat;
    }

    public function setDateTimeFormat(string $dateTimeFormat): void
    {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    public function getMaxDateTime(): string
    {
        return $this->maxDateTime;
    }

    public function setMaxDateTime(string $maxDateTime): void
    {
        $this->maxDateTime = $maxDateTime;
    }

    public function getMinDateTime(): string
    {
        return $this->minDateTime;
    }

    public function setMinDateTime(string $minDateTime): void
    {
        $this->minDateTime = $minDateTime;
    }
}
