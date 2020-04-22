<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Model;

use HeimrichHannot\FieldpaletteBundle\Model\FieldPaletteModel;

/**
 * Class FilterPreselectModel.
 *
 * @property int    $element
 * @property string $initialValueType
 * @property mixed  $initialValue
 * @property array  $initialValueArray
 */
class FilterPreselectModel extends FieldPaletteModel
{
    protected static $strTable = 'tl_filter_preselect';
}
