<?php

namespace HeimrichHannot\FilterBundle\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Input;
use HeimrichHannot\FilterBundle\ContentElement\ContentFilterHyperlink;
use HeimrichHannot\FilterBundle\Controller\ContentElement\FilterPreselectElementController;

class ContentContainer
{
    /**
     * @Callback(table="tl_content", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== Input::get('act')) {
            return;
        }

        $element = ContentModel::findByPk($dc->id);
        if (!$element || !in_array($element->type, [FilterPreselectElementController::TYPE, ContentFilterHyperlink::TYPE]) || !$element->filterConfig) {
            return;
        }

        PaletteManipulator::create()
            ->addField(['filterPreselect', 'filterReset', 'filterPreselectNoRedirect', 'filterPreselectJumpTo'], 'filterConfig', PaletteManipulator::POSITION_AFTER)
            ->applyToPalette(FilterPreselectElementController::TYPE, $dc->table);

        $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_hyperlink'] = str_replace('filterConfig;', 'filterConfig,filterPreselect,filterReset;', $GLOBALS['TL_DCA']['tl_content']['palettes']['filter_hyperlink']);
    }
}