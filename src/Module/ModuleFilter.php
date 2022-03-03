<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Module;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\Module;
use Contao\System;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Event\FilterBeforeRenderFilterFormEvent;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\TwigSupportBundle\Renderer\TwigTemplateRenderer;

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
            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FMD'][$this->type][0].' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        // Hide list and show reader on detail pages if configured
        if ('1' === $this->filter_hideOnAutoItem && (Config::get('useAutoItem') && isset($_GET['auto_item']))) {
            return '';
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

        $this->Template->filter = $this->config;

        $context = [
            'filter' => $this->config,
            'form' => $form->createView(),
            'preselectUrl' => !empty($this->config->getData()) ? $this->config->getPreselectAction($this->config->getData(), true) : '',
        ];

        /** @var FilterBeforeRenderFilterFormEvent $event */
        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new FilterBeforeRenderFilterFormEvent(
                $this->config->getFilterTemplateByName($filter['template']),
                $context,
                $this->config
            )
        );

        $this->Template->preselectUrl = !empty($this->config->getData()) ? $this->config->getPreselectAction($this->config->getData(), true) : '';
        $this->Template->form = System::getContainer()->get(TwigTemplateRenderer::class)->render(
            $event->getTemplate(),
            $event->getContext()
        );
    }
}
