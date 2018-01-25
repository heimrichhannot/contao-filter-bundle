<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Image;
use Contao\StringUtil;

class ListConfig extends Backend
{
    const SORTING_MODE_FIELD = 'field';
    const SORTING_MODE_TEXT = 'text';
    const SORTING_MODE_RANDOM = 'random';

    const SORTING_MODES = [
        self::SORTING_MODE_FIELD,
        self::SORTING_MODE_TEXT,
        self::SORTING_MODE_RANDOM,
    ];

    const SORTING_DIRECTION_ASC = 'asc';
    const SORTING_DIRECTION_DESC = 'desc';

    const SORTING_DIRECTIONS = [
        self::SORTING_DIRECTION_ASC,
        self::SORTING_DIRECTION_DESC,
    ];

    /**
     * Return the edit header button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->canEditFieldsOf('tl_list_config') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}
