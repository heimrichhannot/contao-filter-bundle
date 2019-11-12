<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Controller;

use Contao\Environment;
use Contao\FrontendIndex;
use Contao\System;
use HeimrichHannot\FilterBundle\Event\ModifyJsonResponseEvent;
use HeimrichHannot\FilterBundle\Exception\HandleFormException;
use HeimrichHannot\FilterBundle\Exception\MissingFilterException;
use HeimrichHannot\FilterBundle\Form\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the filter frontend ajax routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class FrontendAjaxController extends Controller
{
    /**
     * @Route("/_filter/ajax_submit/{id}", name="filter_frontend_ajax_submit")
     *
     * @param Request $request Current request
     * @param int     $id      Filter id
     *
     * @throws HandleFormException
     * @throws MissingFilterException
     *
     * @return Response
     */
    public function ajaxSubmitAction(Request $request, int $id): Response
    {
        $this->get('contao.framework')->initialize();

        if (null === ($filter = $this->get('huh.filter.manager')->findById($id))) {
            throw new MissingFilterException('A filter with id '.$id.' does not exist.');
        }

        if (null === ($response = $filter->handleForm())) {
            throw new HandleFormException('Unable to handle form for filter with id '.$id.'.');
        }

        if ($request->get($filter->getFilter()['name']) && isset($request->get($filter->getFilter()['name'])[FilterType::FILTER_REFERRER_NAME])) {
            Environment::set('request', $request->get($filter->getFilter()['name'])[FilterType::FILTER_REFERRER_NAME]);
        }

        $index = new FrontendIndex(); // initialize BE_USER_LOGGED_IN or FE_USER_LOGGED_IN

        $response = new JsonResponse();

        if (null === $filter->getBuilder()) {
            $filter->buildForm($filter->getData());
        }

        $form = $filter->getBuilder()->getForm();

        /**
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');

        $filterConfig = $filter;
        $filter = $twig->render(
            $filter->getFilterTemplateByName($filter->getFilter()['template']),
            [
                'filter' => $filter,
                'form' => $form->createView(),
            ]
        );

        $response->setData(['filter' => $filter, 'filterName' => $request->get('filterName')]);

        $event = $this->container->get('event_dispatcher')->dispatch(ModifyJsonResponseEvent::NAME,
            new ModifyJsonResponseEvent($response, $filterConfig));

        return $event->getResponse();
    }
}
