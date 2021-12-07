<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;

/**
 * @deprecated since 1.12 and will be removed in version 2.0
 */
class RadiusChoiceType extends ChoiceType
{
    const TYPE = 'radius_choice';

    // TODO make configurable
    const RADIUS_STEPS = [
        '1km',
        '5km',
        '10km',
        '25km',
        '50km',
        '100km',
        '200km',
    ];

    public function buildQuery(FilterQueryBuilder $builder, FilterConfigElementModel $element)
    {
    }

    public function getChoices(FilterConfigElementModel $element)
    {
        return array_combine(static::RADIUS_STEPS, static::RADIUS_STEPS);
    }
}
