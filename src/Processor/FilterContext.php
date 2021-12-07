<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Processor;

use HeimrichHannot\FilterBundle\Model\FilterConfigModel;

class FilterContext
{
    /**
     * @var FilterConfigModel
     */
    protected $filterConfigModel;

    /**
     * @var array
     */
    protected $formData;

    /**
     * FrontendAjaxController constructor.
     */
    public function __construct(FilterConfigModel $filterConfigModel, array $formData)
    {
        $this->filterConfigModel = $filterConfigModel;
        $this->formData = $formData;
    }

    public function getInitialData()
    {
    }

    public function getFilterConfigModel(): FilterConfigModel
    {
        return $this->filterConfigModel;
    }

    public function setFilterConfigModel(FilterConfigModel $filterConfigModel): void
    {
        $this->filterConfigModel = $filterConfigModel;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function setFormData(array $formData): void
    {
        $this->formData = $formData;
    }
}
