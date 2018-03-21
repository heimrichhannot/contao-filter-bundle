<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Module;

use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
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
            $objTemplate           = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]) . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

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

        $twig->hasExtension('\Twig_Extensions_Extension_Text') ?: $twig->addExtension(new \Twig_Extensions_Extension_Text());
        $twig->hasExtension('\Twig_Extensions_Extension_Intl') ?: $twig->addExtension(new \Twig_Extensions_Extension_Intl());
        $twig->hasExtension('\Twig_Extensions_Extension_Array') ?: $twig->addExtension(new \Twig_Extensions_Extension_Array());
        $twig->hasExtension('\Twig_Extensions_Extension_Date') ?: $twig->addExtension(new \Twig_Extensions_Extension_Date());

        $templates = System::getContainer()->get('huh.filter.choice.template')->getChoices();

        $this->Template->filter = $this->config;

        $this->Template->form = $twig->render(
            $templates[$filter['template']],
            [
                'filter' => $this->config,
                'form'   => $form->createView(),
            ]
        );
    }
}
