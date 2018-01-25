<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Pagination;

use Contao\System;
use Contao\Template;

class RandomPagination extends \Contao\Pagination
{
    const PARAM_RANDOM = 'random';

    protected $randomSeed = false;

    public function __construct(
        $randomSeed,
        $rows,
        $perPage,
        $numberOfLinks = 7,
        $parameter = 'page',
        Template $template = null,
        $forceParam = false
    ) {
        $this->randomSeed = $randomSeed;

        parent::__construct($rows, $perPage, $numberOfLinks, $parameter, $template, $forceParam);
    }

    protected function linkToPage($page)
    {
        $urlUtil = System::getContainer()->get('huh.utils.url');

        $url = ampersand($this->strUrl);

        if ($page <= 1 && !$this->blnForceParam) {
            if ($this->randomSeed) {
                $url = $urlUtil->addQueryString(static::PARAM_RANDOM.'='.$this->randomSeed, $url);
            }

            return $url;
        }
        $url = $urlUtil->addQueryString($this->strParameter.'='.$page, $url);

        if ($this->randomSeed) {
            $url = $urlUtil->addQueryString(static::PARAM_RANDOM.'='.$this->randomSeed, $url);
        }

        return $url;
    }
}
