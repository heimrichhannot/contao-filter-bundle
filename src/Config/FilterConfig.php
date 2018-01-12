<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Config;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use HeimrichHannot\FilterBundle\Filter\TypeInterface;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Model\FilterElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;

class FilterConfig
{
    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var array|null
     */
    protected $filter;

    /**
     * @var array|null
     */
    protected $elements;

    /**
     * @var FormBuilderInterface|null
     */
    protected $builder;

    /**
     * @var string
     */
    protected $formName;

    /**
     * @var FilterQueryBuilder
     */
    protected $queryBuilder;

    /**
     * Init the filter based on its model
     * @param string $cacheKey
     * @param array $filter
     * @param array|null $elements
     */
    public function init(string $cacheKey, array $filter, $elements = null)
    {
        $this->cacheKey = $cacheKey;
        $this->filter   = $filter;
        $this->elements = $elements;
    }

    /**
     * Build the form
     * @param array $data
     */
    public function buildForm(array $data = [])
    {
        if ($this->filter === null) {
            return;
        }

        $factory = Forms::createFormFactoryBuilder()->addExtensions([])->getFormFactory();

        $options = ['filter' => $this];

        $this->formName = FilterType::$blockPrefix . $this->filter['id'];
        $this->builder  = $factory->createNamedBuilder($this->formName, FilterType::class, $data, $options);
    }

    /**
     *
     */
    public function initQueryBuilder()
    {
        $this->queryBuilder = System::getContainer()->get('huh.filter.query_builder');

        if (!is_array($this->getElements())) {
            return;
        }

        $config = \System::getContainer()->getParameter('huh');

        if (!isset($config['filter']['types'])) {
            return;
        }

        $this->queryBuilder->from($this->getFilter()['dataContainer']);

        foreach ($this->getElements() as $element) {

            if (!isset($config['filter']['types'][$element['type']])) {
                continue;
            }

            $class = $config['filter']['types'][$element['type']];

            if (!class_exists($class)) {
                continue;
            }

            /**
             * @var $type TypeInterface
             */
            $type = new $class($this);

            if (!is_subclass_of($type, \HeimrichHannot\FilterBundle\Filter\AbstractType::class) || !is_subclass_of($type, TypeInterface::class)) {
                continue;
            }

            $type->buildQuery($this->queryBuilder, $element);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->filter['id'];
    }

    /**
     * @return array|null
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return array|null
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return null|FormBuilderInterface
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * @return FilterQueryBuilder|null
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Get the filter data (e.g. form submission data)
     * @return array
     */
    public function getData(): array
    {
        return System::getContainer()->get('huh.filter.session')->getData($this->getCacheKey());
    }
}