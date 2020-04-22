<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\ContentElement;

use Contao\ContentElement;
use Contao\Controller;
use Contao\System;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;

class ContentFilterPreselect extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_filter_initial';

    public function generate()
    {
        if (System::getContainer()->get('huh.utils.container')->isBackend()) {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = implode("\n", $this->getWildcard());
            $objTemplate->title = $this->getFilterTitle();

            return $objTemplate->parse();
        }

        $this->preselect();

        return parent::generate();
    }

    /**
     * Get the wildcard from preselection.
     */
    protected function getWildcard(): array
    {
        $wildcard = [];

        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($this->filterConfig)) || null === ($elements = $filterConfig->getElements())) {
            return $wildcard;
        }

        /** @var FilterPreselectModel $preselections */
        $preselections = System::getContainer()->get('contao.framework')->createInstance(FilterPreselectModel::class);

        if (null === ($preselections = $preselections->findPublishedByPidAndTableAndField($this->id, 'tl_content', 'filterPreselect'))) {
            return $wildcard;
        }

        /** @var FilterPreselectModel $preselection */
        foreach ($preselections as $preselection) {
            $wildcard[] = System::getContainer()->get('huh.filter.backend.filter_preselect')->adjustLabel($preselection->row(), $preselection->id);
        }

        return $wildcard;
    }

    /**
     * Get the filter title.
     */
    protected function getFilterTitle(): string
    {
        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($this->filterConfig)) || null === ($elements = $filterConfig->getElements())) {
            return '';
        }

        return $filterConfig->getFilter()['title'] ?? '';
    }

    /**
     * Invoke preselection.
     */
    protected function preselect()
    {
        if (null === ($filterConfig = System::getContainer()->get('huh.filter.manager')->findById($this->filterConfig)) || null === ($elements = $filterConfig->getElements())) {
            return;
        }

        /** @var FilterPreselectModel $preselections */
        $preselections = System::getContainer()->get('contao.framework')->createInstance(FilterPreselectModel::class);

        $url = $filterConfig->getUrl();

        if ($this->filterPreselectJumpTo &&
            null !== ($jumpTo = System::getContainer()->get('huh.utils.url')->getJumpToPageObject($this->filterPreselectJumpTo, false))) {
            $url = $jumpTo->getFrontendUrl();
        }

        if (null === ($preselections = $preselections->findPublishedByPidAndTableAndField($this->id, 'tl_content', 'filterPreselect'))) {
            $filterConfig->resetData(); // reset previous filters

            if (true === (bool) $this->filterReset && true !== (bool) $this->filterPreselectNoRedirect) {
                Controller::redirect($url);
            }

            return;
        }

        $data = System::getContainer()->get('huh.filter.util.filter_preselect')->getPreselectData($this->filterConfig, $preselections->getModels());

        if (true === (bool) $this->filterReset) {
            $filterConfig->resetData();
        } else {
            $filterConfig->setData($data);
        }

        if (true !== (bool) $this->filterPreselectNoRedirect) {
            Controller::redirect($url);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function compile()
    {
    }
}
