<?php

namespace HeimrichHannot\FilterBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use HeimrichHannot\FilterBundle\Event\FilterBeforeRenderFilterFormEvent;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\TwigSupportBundle\Renderer\TwigTemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @FrontendModule(FilterModuleController::TYPE, category="filter", template="mod_filter")
 */
class FilterModuleController extends AbstractFrontendModuleController
{
    use PageAssetsTrait;

    public const TYPE = 'filter';

    private FilterManager $filterManager;
    private EventDispatcherInterface $eventDispatcher;
    private TwigTemplateRenderer $twigTemplateRenderer;

    public function __construct(FilterManager $filterManager, EventDispatcherInterface $eventDispatcher, TwigTemplateRenderer $twigTemplateRenderer)
    {

        $this->filterManager = $filterManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->twigTemplateRenderer = $twigTemplateRenderer;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        if ('1' === $model->filter_hideOnAutoItem && (Config::get('useAutoItem') && isset($_GET['auto_item']))) {
            return new Response();
        }

        if (!$model->filter) {
            return new Response();
        }

        if (null === ($config = $this->filterManager->findById($model->filter))) {
            return new Response();
        }

        $config->handleRequest();

        $filter = $config->getFilter();

        if (null === $config->getBuilder()) {
            $config->buildForm($config->getData());
        }

        $form = $config->getBuilder()->getForm();

        $template->filter = $config;

        $context = [
            'filter' => $config,
            'form' => $form->createView(),
            'preselectUrl' => !empty($config->getData()) ? $config->getPreselectAction($config->getData(), true) : '',
        ];

        /** @var FilterBeforeRenderFilterFormEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new FilterBeforeRenderFilterFormEvent(
                $config->getFilterTemplateByName($filter['template']),
                $context,
                $config
            )
        );

        $template->preselectUrl = !empty($config->getData()) ? $config->getPreselectAction($config->getData(), true) : '';
        $template->form = $this->twigTemplateRenderer->render(
            $event->getTemplate(),
            $event->getContext()
        );

        $this->addPageEntrypoint('contao-filter-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-filter-bundle' => 'bundles/heimrichhannotcontaofilter/js/contao-filter-bundle.js',
            ],
        ]);

        return $template->getResponse();
    }
}