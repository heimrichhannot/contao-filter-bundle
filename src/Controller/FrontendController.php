<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Controller;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Validator;
use HeimrichHannot\FilterBundle\Exception\HandleFormException;
use HeimrichHannot\FilterBundle\Exception\MissingFilterException;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

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
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(ContaoFramework $framework, FilterManager $filterManager, Utils $utils, RouterInterface $router)
    {
        $this->framework = $framework;
        $this->filterManager = $filterManager;
        $this->utils = $utils;
        $this->router = $router;
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

        $url = $filter->getUrl();

        if (empty($url)) {
            if (
                isset($data[FilterType::FILTER_REFERRER_NAME])
                && !empty($data[FilterType::FILTER_REFERRER_NAME])
                && Validator::isUrl($data[FilterType::FILTER_REFERRER_NAME])
                && $this->utils->string()->startsWith($data[FilterType::FILTER_REFERRER_NAME], $request->getSchemeAndHttpHost())
            ) {
                try {
                    $this->router->match(parse_url($data[FilterType::FILTER_REFERRER_NAME], \PHP_URL_PATH));
                    $url = $data[FilterType::FILTER_REFERRER_NAME];
                } catch (ResourceNotFoundException $e) {
                }
            }
        }

        if (empty($url)) {
            throw new PageNotFoundException();
        }

        $response = new RedirectResponse($url, 303);

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
