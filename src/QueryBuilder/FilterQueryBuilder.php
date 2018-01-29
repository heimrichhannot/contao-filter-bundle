<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Haste\Model\Relations;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\DateType;

class FilterQueryBuilder extends QueryBuilder
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework, Connection $connection)
    {
        parent::__construct($connection);
        $this->framework = $framework;
    }

    /**
     * Add where clause based on an element.
     *
     * @param array        $element
     * @param string       $name    The field name
     * @param FilterConfig $config
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereElement(array $element, string $name, FilterConfig $config)
    {
        $filter = $config->getFilter();

        \Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']])) {
            return $this;
        }

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']];

        if((isset($element['startField']) && '' != $element['startField']) || (isset($element['stopField']) && '' != $element['stopField']))
		{
			$this->whereRangeWidget($element, $name, $config, $dca);
			
			return $this;
		}
        
        
        switch ($dca['inputType']) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $this->whereTagWidget($element, $name, $config, $dca);
                break;
            default:
                $this->whereWidget($element, $name, $config, $dca);
        }

        return $this;
    }
	
	/**
	 * build where query for range
	 *
	 * @param array        $element
	 * @param string       $name
	 * @param FilterConfig $config
	 * @param array        $dca
	 *
	 * @return $this
	 */
	protected function whereRangeWidget(array $element, string $name, FilterConfig $config, array $dca)
	{
		$data = $config->getData();
		$value = $data[$name];
		
		if(null === $value)
		{
			return $this;
		}
		
		if($value->date)
		{
			$value = strtotime($value->date);
		}
		
		if($element['field'] == $element['startField'] && false !== strpos($name, DateType::START_SUFFIX))
		{
			$this->andWhere($this->expr()->gte($element['startField'], $value));
		}

		if($element['field'] == $element['stopField'] && false !== strpos($name, DateType::STOP_SUFFIX))
		{
			$this->andWhere($this->expr()->lte($element['stopField'], $value));
		}

		return $this;
    }
    
    /**
     * Add tag widget where clause.
     *
     * @param array        $element
     * @param string       $name    The field name
     * @param FilterConfig $config
     * @param array        $dca
     *
     * @return $this this FilterQueryBuilder instance
     */
    public function whereWidget(array $element, string $name, FilterConfig $config, array $dca)
    {
        $data = $config->getData();
        $value = $data[$name];

        if (null === $value) {
            return $this;
        }
        
        
		$this->andWhere($this->expr()->like($name, $this->expr()->literal('%'.$value.'%')));

        return $this;
    }

    /**
     * Add tag widget where clause.
     *
     * @param array        $element
     * @param string       $name    The field name
     * @param FilterConfig $config
     * @param array        $dca
     *
     * @return $this this FilterQueryBuilder instance
     */
    protected function whereTagWidget(array $element, string $name, FilterConfig $config, array $dca)
    {
        $filter = $config->getFilter();
        $data = $config->getData();
        $value = $data[$name];
        $relation = Relations::getRelation($filter['dataContainer'], $element['field']);

        if (false === $relation || null === $value) {
            return $this;
        }

        $alias = $relation['table'].'_'.$name;

        $this->join($relation['reference_table'], $relation['table'], $alias, $alias.'.'.$relation['reference_field'].'='.$relation['reference_table'].'.'.$relation['reference']);
        $this->andWhere($this->expr()->in($alias.'.'.$relation['related_field'], $value));

        return $this;
    }
}
