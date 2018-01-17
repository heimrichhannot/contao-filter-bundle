<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\FilterBundle\Temporal\Choice;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;
use HeimrichHannot\NewsBundle\Model\NewsModel;

class YearChoice extends AbstractChoice
{
    /**
     * @return array
     */
    protected function collect()
    {
        $choices = [];
        $newsArchives = deserialize($this->filter->getModule()->news_archives, true);

        if (empty($newsArchives)) {
            return $choices;
        }

        $choices = NewsModel::getPublishedYearsByPids($newsArchives);
        $choices = array_combine($choices, $choices);

        return $choices;
    }
}
