<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Filter\Type;

use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;

class CategoryChoiceType extends ChoiceType
{
    const TYPE = 'category_choise';

    protected $fieldOptions;

    public function __construct(FilterConfig $config)
    {
        parent::__construct($config);
        $this->fieldOptions = System::getContainer()->get('huh.filter.choice.field_options');
    }

    public function getChoices(FilterConfigElementModel $element)
    {
        $options = $this->fieldOptions->getCachedChoices([
            'element' => $element,
            'filter' => $this->config->getFilter(),
        ]);

        return $options;
    }
}
