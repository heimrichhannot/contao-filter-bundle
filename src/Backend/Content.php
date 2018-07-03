<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Backend;


use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;

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
     * Invoke onload_callback
     * @param DataContainer $dc
     */
    public function onLoad(DataContainer $dc){

        if (null === ($content = \Contao\System::getContainer()->get('huh.utils.model')->findModelInstanceByPk($dc->table, $dc->id))) {
            return;
        }

        $this->toggleFilterPreselect($content, $dc);
    }

    /**
     * Toggle filterPreselect field on demand
     * @param ContentModel  $content
     * @param DataContainer $dc
     */
    protected function toggleFilterPreselect(ContentModel $content, DataContainer $dc)
    {
        if($content->filter < 1)
        {
            return;
        }

        $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_preselect'] = str_replace('filter;', 'filter,filterPreselect,filterPreselectNoRedirect;', $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_preselect']);
        $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_hyperlink'] = str_replace('filter;', 'filter,filterPreselect;', $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_hyperlink']);
    }
}