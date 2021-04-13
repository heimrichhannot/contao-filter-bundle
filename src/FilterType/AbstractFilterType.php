<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

use HeimrichHannot\FilterBundle\Filter\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\Filter\FilterQueryPartProcessor;

abstract class AbstractFilterType implements FilterTypeInterface
{
    const GROUP_DEFAULT = 'miscellaneous';

    /**
     * @var FilterQueryPartProcessor
     */
    protected $filterQueryPartProcessor;

    /**
     * @var FilterQueryPartCollection
     */
    protected $filterQueryPartCollection;

    /**
     * @var FilterTypeContext
     */
    private $context;

    /**
     * @var string
     */
    private $group = '';

    public function __construct(FilterQueryPartProcessor $filterQueryPartProcessor, FilterQueryPartCollection $filterQueryPartCollection)
    {
        $this->initialize();
        $this->filterQueryPartProcessor = $filterQueryPartProcessor;
        $this->filterQueryPartCollection = $filterQueryPartCollection;
    }

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

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.$appendPalette;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    protected function initialize(): void
    {
        if (empty($this->group) && !\defined('static::GROUP')) {
            $this->setGroup(static::GROUP_DEFAULT);
        } else {
            $this->setGroup(static::GROUP);
        }
    }

    private function setDefaultContext()
    {
        $this->context = new FilterTypeContext();
    }
}
