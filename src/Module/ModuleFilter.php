<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Module;

use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use Patchwork\Utf8;

class ModuleFilter extends Module
{
    const TYPE = 'filter';

    protected $strTemplate = 'mod_filter';

    /**
     * @var FilterConfig
     */
    protected $config;

    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        if (!System::getContainer()->has('huh.filter.manager') || !$this->objModel->filter) {
            return '';
        }

        /** @var FilterManager $registry */
        $registry = System::getContainer()->get('huh.filter.manager');

        if (null === ($this->config = $registry->findById($this->objModel->filter))) {
            return '';
        }

        $this->config->handleRequest();

        return parent::generate();
    }

    /**
     * {@inheritdoc}
     */
    protected function compile()
    {
        $filter = $this->config->getFilter();

        if (null === $this->config->getBuilder()) {
            $this->config->buildForm($this->config->getData());
        }

        $form = $this->config->getBuilder()->getForm();

        /**
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');

        $this->Template->filter = $this->config;

        $this->Template->form = $twig->render(
            $this->config->getFilterTemplateByName($filter['template']),
            [
                'filter' => $this->config,
                'form' => $form->createView(),
            ]
        );
    }
}
