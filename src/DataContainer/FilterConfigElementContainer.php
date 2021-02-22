<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeCollection;

class FilterConfigElementContainer
{
    const GROUP_DEFAULT = 'miscellaneous';

    /**
     * @var array
     */
    protected $bundleConfig;
    /**
     * @var TypeChoice
     */
    protected $typeChoice;
    /**
     * @var FilterTypeCollection
     */
    protected $typeCollection;

    public function __construct(array $bundleConfig, TypeChoice $typeChoice, FilterTypeCollection $typeCollection)
    {
        $this->bundleConfig = $bundleConfig;
        $this->typeChoice = $typeChoice;
        $this->typeCollection = $typeCollection;
    }

    public function getTypeOptions(DataContainer $dc)
    {
        if ($this->bundleConfig['filter']['disable_legacy_filters']) {
            $options = [];

            foreach ($this->typeCollection->getTypes() as $key => $type) {
                $group = $type->getGroup();

                if (empty($group)) {
                    $group = static::GROUP_DEFAULT;
                }
                $options[$group][] = $key;
            }

            return $options;
        }

        return $this->typeChoice->getCachedChoices($dc);
    }
}
