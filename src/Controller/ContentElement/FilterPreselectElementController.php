<?php

namespace HeimrichHannot\FilterBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\PageModel;
use Contao\Template;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterPreselectModel;
use HeimrichHannot\FilterBundle\Util\FilterPreselectUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(FilterPreselectElementController::TYPE, category="filter", template="ce_filter_initial")
 */
class FilterPreselectElementController extends AbstractContentElementController
{
    public const TYPE = 'filter_preselect';

    private FilterManager $filterManager;
    private FilterPreselectUtil $preselectUtil;

    public function __construct(FilterManager $filterManager, FilterPreselectUtil $preselectUtil)
    {
        $this->filterManager = $filterManager;
        $this->preselectUtil = $preselectUtil;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        if (null === ($filterConfig = $this->filterManager->findById($model->filterConfig)) || null === ($elements = $filterConfig->getElements())) {
            return $template->getResponse();
        }

        if ($this->container->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $template->getResponse();
        }

        $preselections = new FilterPreselectModel();
        $url = $filterConfig->getUrl();

        if ($model->filterPreselectJumpTo) {
            $jumpToPageModel = PageModel::findByPk($model->filterPreselectJumpTo);
            if ($jumpToPageModel) {
                $url = $jumpToPageModel->getAbsoluteUrl();
            }
        }

        if (null === ($preselections = $preselections->findPublishedByPidAndTableAndField($model->id, 'tl_content', 'filterPreselect'))) {
            $filterConfig->resetData(); // reset previous filters

            if (true === (bool) $model->filterReset && true !== (bool) $model->filterPreselectNoRedirect) {
                return new RedirectResponse($url);
            }

            return $template->getResponse();
        }

        $data = $this->preselectUtil->getPreselectData($model->filterConfig, $preselections->getModels());

        if (true === (bool) $model->filterReset) {
            $filterConfig->resetData();
        } else {
            $filterConfig->setData($data);
        }

        if (true !== (bool) $model->filterPreselectNoRedirect) {
            return new RedirectResponse($url);
        }

        return $template->getResponse();
    }
}