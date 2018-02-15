<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Module;

use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Registry\FilterRegistry;
use Patchwork\Utf8;

class ModuleFilter extends \Contao\Module
{
    protected $strTemplate = 'mod_filter';

    /**
     * @var FilterConfig
     */
    protected $config;

    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        /**
         * @var FilterRegistry
         */
        $registry = System::getContainer()->get('huh.filter.registry');

        if (null === ($config = $registry->findById($this->objModel->filter))) {
            return '';
        }

        $this->config = $config;

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

        $templates = System::getContainer()->get('huh.filter.choice.template')->getChoices();

        $this->Template->filter = $this->config;

        $this->Template->form = $twig->render(
            $templates[$filter['template']],
            [
                'filter' => $this->config,
                'form' => $form->createView(),
            ]
        );
    }
}
