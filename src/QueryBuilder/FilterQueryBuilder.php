<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\QueryBuilder;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Haste\Model\Model;
use Haste\Model\Relations;
use HeimrichHannot\FilterBundle\Config\FilterConfig;

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
     * Add where clause based on an element
     * @param array $element
     * @param FilterConfig $config
     * @return $this This FilterQueryBuilder instance.
     */
    public function whereElement(array $element, FilterConfig $config)
    {
        $filter = $config->getFilter();

        \Controller::loadDataContainer($filter['dataContainer']);

        if (!isset($GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']])) {
            return $this;
        }

        $dca = $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$element['field']];

        switch ($dca['inputType']) {
            case 'cfgTags':
                if (!isset($dca['eval']['tagsManager'])) {
                    break;
                }
                $this->whereTagWidget($element, $config, $dca);
                break;
            default:
                $this->whereWidget($element, $config, $dca);
        }

        return $this;
    }

    /**
     * Add tag widget where clause
     * @param array $element
     * @param FilterConfig $config
     * @param array $dca
     *
     * @return $this This FilterQueryBuilder instance.
     */
    protected function whereTagWidget(array $element, FilterConfig $config, array $dca)
    {
        $filter   = $config->getFilter();
        $data     = $config->getData();
        $value    = $data[$element['field']];
        $relation = Relations::getRelation($filter['dataContainer'], $element['field']);

        if ($relation === false || $value === null) {
            return $this;
        }

        $this->join($relation['reference_table'], $relation['table'], $relation['table'], $relation['table'] . '.' . $relation['reference_field'] . '=' . $relation['reference_table'] . '.' . $relation['reference']);
        $this->andWhere($this->expr()->in($relation['table'] . '.' . $relation['related_field'], $value));

        return $this;
    }

    /**
     * Add tag widget where clause
     * @param array $element
     * @param FilterConfig $config
     * @param array $dca
     *
     * @return $this This FilterQueryBuilder instance.
     */
    public function whereWidget(array $element, FilterConfig $config, array $dca)
    {


        return $this;
    }
}