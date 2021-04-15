<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

use Contao\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class FilterTypeContext implements \IteratorAggregate
{
    /**
     * @var string
     */
    private $defaultValue = '';
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
     * @var QueryBuilder
     */
    private $queryBuilder;
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $value = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        if (empty($this->value)) {
            return $this->getDefaultValue();
        }

        return $this->value;
    }

    public function setValue(string $value): void
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

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }
}
