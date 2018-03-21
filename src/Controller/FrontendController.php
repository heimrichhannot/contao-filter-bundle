<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Controller;


use Contao\FrontendCron;
use HeimrichHannot\FilterBundle\Exception\HandleFormException;
use HeimrichHannot\FilterBundle\Exception\MissingFilterException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Handles the filter frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class FrontendController extends Controller
{

    /**
     * @Route("/_filter/submit/{id}", name="filter_frontend_submit")
     *
     * @param int $id Filter id
     *
     * @throws MissingFilterException
     * @throws HandleFormException
     * @return RedirectResponse
     */
    public function submitAction(int $id): RedirectResponse
    {
        $this->get('contao.framework')->initialize();

        if (null === ($filter = $this->get('huh.filter.manager')->findById($id))) {
            throw new MissingFilterException('A filter with id ' . $id . ' does not exist.');
        }

        if (null === ($response = $filter->handleForm())) {
            throw new HandleFormException('Unable to handle form for filter with id ' . $id . '.');
        }

        return $response;
    }
}