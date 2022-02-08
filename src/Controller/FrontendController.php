<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use HeimrichHannot\FilterBundle\Exception\HandleFormException;
use HeimrichHannot\FilterBundle\Exception\MissingFilterException;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the filter frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class FrontendController extends AbstractController
{
    /**
     * @var ContaoFramework
     */
    protected $framework;
    /**
     * @var FilterManager
     */
    protected $filterManager;

    public function __construct(ContaoFramework $framework, FilterManager $filterManager)
    {
        $this->framework = $framework;
        $this->filterManager = $filterManager;
    }

    /**
     * @Route("/_filter/preselect/{id}", name="filter_frontend_preselect")
     *
     * @param Request $request Current request
     * @param int     $id      Filter id
     *
     * @throws HandleFormException
     * @throws MissingFilterException
     */
    public function preselectAction(Request $request, int $id): RedirectResponse
    {
        $this->framework->initialize();

        if (null === ($filter = $this->filterManager->findById($id))) {
            throw new MissingFilterException('A filter with id '.$id.' does not exist.');
        }

        $data = $request->query->get('data');

        $filter->setData(\is_array($data) ? $data : []);

        $response = new RedirectResponse($filter->getUrl(), 303);

        return $response;
    }

    /**
     * @Route("/_filter/submit/{id}", name="filter_frontend_submit")
     *
     * @param Request $request Current request
     * @param int     $id      Filter id
     *
     * @throws HandleFormException
     * @throws MissingFilterException
     */
    public function submitAction(Request $request, int $id): Response
    {
        $this->framework->initialize();

        if (null === ($filter = $this->filterManager->findById($id))) {
            throw new MissingFilterException('A filter with id '.$id.' does not exist.');
        }

        if (null === ($response = $filter->handleForm())) {
            throw new HandleFormException('Unable to handle form for filter with id '.$id.'.');
        }

        return $response;
    }
}
