<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Backend;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Model;

class Content
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Invoke onload_callback.
     *
     * @param DataContainer $dc
     */
    public function onLoad(DataContainer $dc)
    {
        if (null === ($content = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $this->toggleFilterPreselect($content, $dc);
    }

    /**
     * Toggle filterPreselect field on demand.
     *
     * @param Model         $content
     * @param DataContainer $dc
     */
    protected function toggleFilterPreselect(Model $content, DataContainer $dc)
    {
        if ($content->filterConfig < 1) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_preselect'] = str_replace('filterConfig;', 'filterConfig,filterPreselect,filterReset,filterPreselectNoRedirect,filterPreselectJumpTo;', $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_preselect']);
        $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_hyperlink'] = str_replace('filterConfig;', 'filterConfig,filterPreselect,filterReset;', $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_hyperlink']);
    }
}
