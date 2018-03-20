<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Controller;


use Symfony\Component\HttpFoundation\Response;

class FrontendController extends \Contao\CoreBundle\Controller\FrontendController
{

    /**
     * @return Response
     */
    public function submit()
    {
        $this->get('contao.framework')->initialize();
    }
}