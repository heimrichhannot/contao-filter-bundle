<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Temporal\Choice;

use HeimrichHannot\FilterBundle\Choice\AbstractChoice;

class MonthChoice extends AbstractChoice
{
    /**
     * Current year
     * @var int
     */
    protected $year;

    /**
     * Current year
     * @param $year
     */
    public function setYear($year)
    {
        $this->year     = $year;
        $this->cacheKey .= $year;

        return $this;
    }

    /**
     * @return array
     */
    protected function collect()
    {
        $choices      = [];
        $newsArchives = deserialize($this->filter->getModule()->news_archives, true);

        if (!empty($newsArchives)) {
            $months = NewsModel::getPublishedMonthsByYearAndPids($newsArchives, $this->year);

            foreach ($months as $month) {
                $choices[$month] = 'news.form.filter.choice.month.' . $month;
            }

            $choices = array_flip($choices);
        }

        return $choices;
    }
}