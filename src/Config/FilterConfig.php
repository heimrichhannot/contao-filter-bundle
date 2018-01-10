<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Config;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Model\FilterElementModel;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;

class FilterConfig
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var array
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
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Init the filter based on its model
     * @param array $filter
     */
    public function init(array $filter)
    {
        $this->filter = $filter;

        /**
         * @var FilterElementModel $adapter
         */
        $adapter = $this->framework->getAdapter(FilterElementModel::class);

        $this->elements = $adapter->findPublishedByPid($this->filter['id']);

        if (null !== $this->elements) {
            $this->elements = $this->elements->fetchAll();
        }
    }

    public function buildForm()
    {
        $factory = Forms::createFormFactoryBuilder()->addExtensions([])->getFormFactory();

        $options = [];

        $this->formName = FilterType::$blockPrefix . $this->filter['id'];
        $this->builder  = $factory->createNamedBuilder($this->formName, FilterType::class, ['filter' => $this->filter['id']], $options);
    }

    protected function getTemplate()
    {
        
    }

    /**
     * @return array
     */
    public function getFilter(): array
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
}