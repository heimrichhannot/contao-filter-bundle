<?php

namespace HeimrichHannot\FilterBundle\Event;

use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\EventDispatcher\Event;

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

    public function __construct(QueryBuilder $queryBuilder, string $name, string $operator, $value, FilterConfigElementModel $element)
    {
        $this->queryBuilder = $queryBuilder;
        $this->operator = $operator;
        $this->value = $value;
        $this->element = $element;
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function getContinue(): bool
    {
        return $this->continue;
    }

    /**
     * @param bool $continue
     */
    public function setContinue(bool $continue): void
    {
        $this->continue = $continue;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
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

    /**
     * @return FilterConfigElementModel
     */
    public function getElement(): FilterConfigElementModel
    {
        return $this->element;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}