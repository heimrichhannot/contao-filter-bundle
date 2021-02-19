<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;

class FilterConfigElementContainer
{
    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TypeChoice
     */
    protected $typeChoice;

    public function __construct(array $bundleConfig, TypeChoice $typeChoice)
    {
        $this->bundleConfig = $bundleConfig;
        $this->typeChoice = $typeChoice;
    }

    public function getTypeOptions(DataContainer $dc)
    {
        if ($this->bundleConfig['filter']['disable_legacy_filters']) {
            return ['text' => ['future_text']];
        }

        return $this->typeChoice->getCachedChoices($dc);
    }
}
