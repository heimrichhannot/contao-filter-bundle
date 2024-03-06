<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\FrontendIndex;
use Contao\System;
use Exception;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\FilterBundle\Exception\HandleFormException;
use HeimrichHannot\FilterBundle\Exception\MissingFilterException;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Util\Polyfill;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment as TwigEnvironment;
use const PHP_URL_HOST;

/**
 * Handles the filter frontend ajax routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true}, name=FrontendAjaxController::ROUTE_NAME_AJAX)
 */
class FrontendAjaxController extends AbstractController
{
    const ROUTE_NAME_AJAX = 'filter_frontend_ajax_submit';

    protected ContaoFramework $framework;
    protected FilterManager $filterManager;
    protected EventDispatcherInterface $eventDispatcher;
    private TwigEnvironment $twig;

    /**
     * FrontendAjaxController constructor.
     */
    public function __construct(
        ContaoFramework $framework,
        FilterManager $filterManager,
        EventDispatcherInterface $eventDispatcher,
        TwigEnvironment $twig
    ) {
        $this->framework = $framework;
        $this->filterManager = $filterManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
    }

    /**
     * @Route("/_filter/ajax_submit/{id}", name=FrontendAjaxController::ROUTE_NAME_AJAX)
     *
     * @param Request $request Current request
     * @param int $id Filter id
     *
     * @throws HandleFormException
     * @throws MissingFilterException
     * @throws Exception
     */
    public function ajaxSubmitAction(Request $request, int $id): Response
    {
        $this->framework->initialize();

        if (null === ($filter = $this->filterManager->findById($id))) {
            throw new MissingFilterException('A filter with id '.$id.' does not exist.');
        }

        global $objPage;

        if (null === $objPage) {
            $pageId = $request->get($filter->getFilter()['name'])[FilterType::FILTER_PAGE_ID_NAME];

            if (is_numeric($pageId)) {
                $objPage = Polyfill::retrieveGlobalPageFromCurrentPageId((int) $pageId);
            }
        }

        if (null === ($response = $filter->handleForm())) {
            throw new HandleFormException('Unable to handle form for filter with id '.$id.'.');
        }

        $nameFilterParam = $request->get($filter->getFilter()['name'] ?? null);
        $nameFilter = $nameFilterParam[FilterType::FILTER_REFERRER_NAME] ?? null;

        if ($nameFilter) {
            if (parse_url($nameFilter, PHP_URL_HOST) !== parse_url(Environment::get('url'), PHP_URL_HOST)) {
                throw new Exception('Invalid redirect url');
            }

            Environment::set('request', $request->get($filter->getFilter()['name'])[FilterType::FILTER_REFERRER_NAME]);
        }

        $index = new FrontendIndex(); // initialize BE_USER_LOGGED_IN or FE_USER_LOGGED_IN

        $response = new JsonResponse();

        if (null === $filter->getBuilder()) {
            $filter->buildForm($filter->getData());
        }

        $builder = $filter->getBuilder();
        $form = $builder->getForm();

        $filterConfig = $filter;

        $filter = $this->twig->render(
            $filter->getFilterTemplateByName($filter->getFilter()['template']),
            [
                'filter' => $filter,
                'form' => $form->createView(),
                'preselectUrl' => !empty($filter->getData()) ? $filter->getPreselectAction($filter->getData(), true) : '',
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
