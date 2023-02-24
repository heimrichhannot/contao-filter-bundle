<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Util\Utils;

class FilterConfigElementContainer
{
    private Utils $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * @Callback(table="tl_filter_config_element", target="fields.field.options")
     */
    public function onFieldOptionsCallback(DataContainer $dc = null): array
    {
        $fields = $this->utils->dca()->getDcaFields($dc->table, [
            'onlyDatabaseFields' => true,
            'localizeLabels' => true,
        ]);

        $options = [];

        foreach ($fields as $field => $label) {
            $options[$field] = $field.' <span style="display: inline; color:#999; padding-left:3px">['.$label.']</span>';
        }

        return $options;
    }
}
