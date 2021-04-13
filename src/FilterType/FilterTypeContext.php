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
     * @var string
     */
    private $name = '';
    /**
     * @var string
     */
    private $value = '';

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var string
     */
    private $operator;
    /**
     * @var Model
     */
    private $parent = null;
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $initial = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
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

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
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
}
