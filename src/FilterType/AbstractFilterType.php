<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

abstract class AbstractFilterType implements FilterTypeInterface
{
    /**
     * @var FilterTypeContext
     */
    private $context;

    /**
     * @var string
     */
    private $group = '';

    public function getContext(): FilterTypeContext
    {
        return $this->context;
    }

    public function setContext(FilterTypeContext $context)
    {
        $this->context = $context;
    }

    public function getPalette(): string
    {
        return '{general_legend},title;{expert_legend},cssClass;{publish_legend},published;';
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }
}
