<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class Filter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        switch ($targetEntity->getReflectionClass()->name) {
            case 'HeimrichHannot/FilterBundle/FilterType/Type/TextType':
                return sprintf('%s.name = %s', $targetTableAlias, $this->getParameter('name'));

                break;
        }

        return '';
    }
}
