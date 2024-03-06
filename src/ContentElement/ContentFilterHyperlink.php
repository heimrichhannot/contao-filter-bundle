<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\ContentElement;

use Contao\ContentHyperlink;
use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;

class ContentFilterHyperlink extends ContentHyperlink
{
    public const TYPE = 'filter_hyperlink';

    /**
     * {@inheritdoc}
     */
    protected function compile(): void
    {
        if (null === ($this->url = $this->getFilterUrl())) {
            return;
        }

        $this->url = StringUtil::ampersand($this->url);

        $embed = explode('%s', $this->embed);

        // Use an image instead of the title
        if ($this->useImage && '' !== $this->singleSRC) {
            $objModel = FilesModel::findByUuid($this->singleSRC);

            if (null !== $objModel && is_file(TL_ROOT.'/'.$objModel->path)) {
                $this->singleSRC = $objModel->path;

                // $this->addImageToTemplate($this->Template, $this->arrData, null, null, $objModel);
                // todo: tis right...?

                /** @var FigureBuilder $figureBuilder */
                $figureBuilder = System::getContainer()->get(FigureBuilder::class);

                $figure = $figureBuilder->from($this->singleSRC)
                    ->setSize($this->size)
                    ->setMetadata($this->objModel->getMetadata())
                    ->setOverwriteMetadata($this->objModel->getOverwriteMetadata())
                    ->buildIfResourceExists();

                $figure->applyLegacyTemplateData($this->Template);

                $this->Template->useImage = true;
            }
        }

        if (0 !== strncmp($this->rel, 'lightbox', 8)) {
            $this->Template->attribute = ' rel="'.$this->rel.'"';
        } else {
            $this->Template->attribute = ' data-lightbox="'.substr($this->rel, 9, -1).'"';
        }

        // Deprecated since Contao 4.0, to be removed in Contao 5.0
        $this->Template->rel = $this->rel;

        if ('' === $this->linkTitle) {
            $this->linkTitle = $this->url;
        }

        $this->Template->href = $this->url;
        $this->Template->embed_pre = $embed[0];
        $this->Template->embed_post = $embed[1];
        $this->Template->link = $this->linkTitle;
        $this->Template->target = '';

        if ($this->titleText) {
            $this->Template->linkTitle = StringUtil::specialchars($this->titleText);
        }

        // Override the link target
        if ($this->target) {
            $this->Template->target = ' target="_blank"';
        }

        // Unset the title attributes in the back end (see #6258)
        if (TL_MODE === 'BE') {
            $this->Template->title = '';
            $this->Template->linkTitle = '';
        }
    }

    /**
     * Get the filter url based on current preselection.
     */
    protected function getFilterUrl(): ?string
    {
        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($this->filterConfig)) || null === ($elements = $filterConfig->getElements())) {
            return null;
        }

        /** @var FilterPreselectModel $preSelections */
        $preSelections = System::getContainer()->get('contao.framework')->createInstance(FilterPreselectModel::class);
        $preSelections = $preSelections->findPublishedByPidAndTableAndField($this->id, 'tl_content', 'filterPreselect');

        if (null === $preSelections) {
            return null;
        }

        $preselectModels = System::getContainer()->get('huh.filter.util.filter_preselect')->getPreselectData($this->filterConfig, $preSelections->getModels());
        $url = $filterConfig->getPreselectAction($preselectModels);
        if (null === $url) {
            return null;
        }

        return $url;
    }
}