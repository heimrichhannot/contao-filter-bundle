<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Contracts\EventDispatcher\Event;

class FilterQueryBuilderComposeEvent extends Event
{
    private $continue = true;
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;
    /**
     * @var string
     */
    private $operator;
    private $value;
    /**
     * @var FilterConfigElementModel
     */
    private $element;
    /**
     * @var string
     */
    private $name;
    /**
     * @var FilterConfig
     */
    private $filterConfig;

    public function __construct(QueryBuilder $queryBuilder, string $name, string $operator, $value, FilterConfigElementModel $element, FilterConfig $filterConfig)
    {
        $this->queryBuilder = $queryBuilder;
        $this->operator = $operator;
        $this->value = $value;
        $this->element = $element;
        $this->name = $name;
        $this->filterConfig = $filterConfig;
    }

    public function getContinue(): bool
    {
        return $this->continue;
    }

    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
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

    public function getElement(): FilterConfigElementModel
    {
        return $this->element;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilterConfig(): FilterConfig
    {
        return $this->filterConfig;
    }
}
