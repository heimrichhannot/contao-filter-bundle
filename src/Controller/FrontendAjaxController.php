<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\FrontendIndex;
use Contao\System;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\FilterBundle\Exception\HandleFormException;
use HeimrichHannot\FilterBundle\Exception\MissingFilterException;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\TwigSupportBundle\Renderer\TwigTemplateRenderer;
use HeimrichHannot\UtilsBundle\Page\PageUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Handles the filter frontend ajax routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class FrontendAjaxController extends AbstractController
{
    /**
     * @var ContaoFramework
     */
    protected $framework;
    /**
     * @var FilterManager
     */
    protected $filterManager;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var PageUtil
     */
    private $pageUtil;

    /**
     * FrontendAjaxController constructor.
     */
    public function __construct(
        PageUtil $pageUtil,
        ContaoFramework $framework,
        FilterManager $filterManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->pageUtil = $pageUtil;
        $this->framework = $framework;
        $this->filterManager = $filterManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/_filter/ajax_submit/{id}", name="filter_frontend_ajax_submit")
     *
     * @param Request $request Current request
     * @param int     $id      Filter id
     *
     * @throws HandleFormException
     * @throws MissingFilterException
     */
    public function ajaxSubmitAction(Request $request, int $id): Response
    {
        $this->framework->initialize();

        if (null === ($filter = $this->filterManager->findById($id))) {
            throw new MissingFilterException('A filter with id '.$id.' does not exist.');
        }

        if (null === ($response = $filter->handleForm())) {
            throw new HandleFormException('Unable to handle form for filter with id '.$id.'.');
        }

        if ($request->get($filter->getFilter()['name']) && isset($request->get($filter->getFilter()['name'])[FilterType::FILTER_REFERRER_NAME])) {
            if (parse_url($request->get($filter->getFilter()['name'])[FilterType::FILTER_REFERRER_NAME], \PHP_URL_HOST) !== parse_url(Environment::get('url'), \PHP_URL_HOST)) {
                throw new \Exception('Invalid redirect url');
            }

            Environment::set('request', $request->get($filter->getFilter()['name'])[FilterType::FILTER_REFERRER_NAME]);
        }

        global $objPage;

        if (null === $objPage) {
            $pageId = $request->get($filter->getFilter()['name'])[FilterType::FILTER_PAGE_ID_NAME];

            if (is_numeric($pageId)) {
                $objPage = $this->pageUtil->retrieveGlobalPageFromCurrentPageId((int) $pageId);
            }
        }

        $index = new FrontendIndex(); // initialize BE_USER_LOGGED_IN or FE_USER_LOGGED_IN

        $response = new JsonResponse();

        if (null === $filter->getBuilder()) {
            $filter->buildForm($filter->getData());
        }

        $builder = $filter->getBuilder();
        $form = $builder->getForm();

        $filterConfig = $filter;

        $filter = System::getContainer()->get(TwigTemplateRenderer::class)->render(
            $filter->getFilterTemplateByName($filter->getFilter()['template']),
            [
                'filter' => $filter,
                'form' => $form->createView(),
                'preselectUrl' => $filter->getPreselectAction($filter->getData(), true),
            ]
        );

        $response->setData(['filter' => $filter, 'filterName' => $request->get('filterName')]);

        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new ModifyJsonResponseEvent($response, $filterConfig),
            ModifyJsonResponseEvent::NAME
        );

        return $event->getResponse();
    }
}
