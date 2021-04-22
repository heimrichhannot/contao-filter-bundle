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
     * @var bool
     */
    private $addInputGroup = false;

    /**
     * @var string
     */
    private $buttonType;

    /**
     * @var string
     */
    private $cssClass;

    /**
     * @var bool
     */
    private $customLabel = false;

    /**
     * @var string
     */
    private $dateTimeFormat;

    /**
     * @var int
     */
    private $debounce = 0;

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
     * @var bool
     */
    private $html5 = false;

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
    private $inputGroupAppend;

    /**
     * @var string
     */
    private $inputGroupPrepend;

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
     * @var bool
     */
    private $submitOnInput = false;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $threshold = 0;

    /**
     * @var string|array|int|\DateTime|\Date
     */
    private $value;

    /**
     * @var string
     */
    private $valueType;

    /**
     * @var string
     */
    private $widget;

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

    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    public function setCssClass(string $class): void
    {
        $this->cssClass = $class;
    }

    public function getButtonType(): string
    {
        return $this->buttonType;
    }

    public function setButtonType(string $buttonType): void
    {
        $this->buttonType = $buttonType;
    }

    public function isCustomLabel(): bool
    {
        return $this->customLabel;
    }

    public function setCustomLabel(bool $customLabel): void
    {
        $this->customLabel = $customLabel;
    }

    public function isHtml5(): bool
    {
        return $this->html5;
    }

    public function setHtml5(bool $html5): void
    {
        $this->html5 = $html5;
    }

    public function getWidget(): string
    {
        return $this->widget;
    }

    public function setWidget(string $widget): void
    {
        $this->widget = $widget;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function setThreshold(int $threshold): void
    {
        $this->threshold = $threshold;
    }

    public function getDebounce(): int
    {
        return $this->debounce;
    }

    public function setDebounce(int $debounce): void
    {
        $this->debounce = $debounce;
    }

    public function isSubmitOnInput(): bool
    {
        return $this->submitOnInput;
    }

    public function setSubmitOnInput(bool $submitOnInput): void
    {
        $this->submitOnInput = $submitOnInput;
    }

    public function hasInputGroup(): bool
    {
        return $this->addInputGroup;
    }

    public function setInputGroup(bool $addInputGroup): void
    {
        $this->addInputGroup = $addInputGroup;
    }

    public function getInputGroupAppend(): string
    {
        return $this->inputGroupAppend;
    }

    public function setInputGroupAppend(string $inputGroupAppend): void
    {
        $this->inputGroupAppend = $inputGroupAppend;
    }

    public function getInputGroupPrepend(): string
    {
        return $this->inputGroupPrepend;
    }

    public function setInputGroupPrepend(string $inputGroupPrepend): void
    {
        $this->inputGroupPrepend = $inputGroupPrepend;
    }

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): void
    {
        $this->valueType = $valueType;
    }
}
