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
        if (!isset($this->context)) {
            $this->setDefaultContext();
        }

        return $this->context;
    }

    public function setContext(FilterTypeContext $context)
    {
        $this->context = $context;
    }

    public function getPalette(): string
    {
        return '{initial_legend},isInitial;{general_legend},title,type;{config_legend},field;{expert_legend},cssClass;{publish_legend},published;';
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    private function setDefaultContext()
    {
        $context = new FilterTypeContext();
        $this->context = $context;
    }
}
