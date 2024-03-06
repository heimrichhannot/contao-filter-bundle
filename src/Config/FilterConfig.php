<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Config;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Environment;
use Contao\Input;
use Contao\InsertTags;
use Contao\System;
use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\Event\FilterConfigInitEvent;
use HeimrichHannot\FilterBundle\Event\FilterFormAdjustOptionsEvent;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Filter\FilterCollection;
use HeimrichHannot\FilterBundle\Filter\Type\PublishedType;
use HeimrichHannot\FilterBundle\Filter\Type\SkipParentsType;
use HeimrichHannot\FilterBundle\Filter\Type\SqlType;
use HeimrichHannot\FilterBundle\Form\Extension\FormButtonExtension;
use HeimrichHannot\FilterBundle\Form\Extension\FormTypeExtension;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\Util\DatabaseUtilPolyfill;
use HeimrichHannot\TwigSupportBundle\Filesystem\TwigTemplateLocator;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class FilterConfig implements \JsonSerializable
{
    const FILTER_TYPE_DEFAULT = 'filter';
    const FILTER_TYPE_SORT = 'sort';

    const FILTER_TYPES = [
        self::FILTER_TYPE_DEFAULT,
        self::FILTER_TYPE_SORT,
    ];

    const QUERY_BUILDER_MODE_INITIAL_ONLY = 'initial_only';
    const QUERY_BUILDER_MODE_SKIP_INITIAL = 'skip_initial';
    const QUERY_BUILDER_MODE_DEFAULT = 'default';

    protected ContaoFramework $framework;
    protected FilterSession $session;
    protected string $sessionKey;
    protected array $resetNames = [];
    protected ?array $filter;
    /**
     * @var \Contao\Model\Collection|FilterConfigElementModel[]|null
     */
    protected mixed $elements;
    protected ?FormBuilderInterface $builder;
    protected FilterQueryBuilder $queryBuilder;
    protected bool $formSubmitted = false;
    private RequestStack $requestStack;
    private Utils $utils;
    private InsertTagParser $insertTagParser;

    public function __construct(
        ContaoFramework $framework,
        FilterSession $session,
        Connection $connection,
        RequestStack $requestStack,
        Utils $utils,
        InsertTagParser $insertTagParser,
        FilterCollection $filterCollection,
        DatabaseUtilPolyfill $dbUtil
    ) {
        $this->framework = $framework;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->utils = $utils;
        $this->insertTagParser = $insertTagParser;

        $this->queryBuilder = new FilterQueryBuilder(
            $framework,
            $connection,
            $insertTagParser,
            $filterCollection,
            $utils,
            $dbUtil
        );
    }

    /**
     * Init the filter based on its model.
     *
     * @param \Contao\Model\Collection|FilterConfigElementModel[]|null $elements
     */
    public function init(string $sessionKey, array $filter, $elements = null): void
    {
        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new FilterConfigInitEvent($filter, $sessionKey, $elements),
            FilterConfigInitEvent::class
        );

        $this->filter = $event->getFilter();
        $this->sessionKey = $event->getSessionKey();
        $this->elements = $event->getElements();
    }

    /**
     * Build the form.
     */
    public function buildForm(array $data = [], array $configuration = []): void
    {
        $configuration = array_merge([
            'overrideFilter' => null,
            'skipSession' => false,
            'skipAjax' => false,
        ], $configuration);

        if ($configuration['overrideFilter']) {
            $filter = $configuration['overrideFilter'];
        } elseif ($this->filter) {
            $filter = $this->filter;
        } else {
            return;
        }

        $factory = Forms::createFormFactoryBuilder()->addTypeExtensions([
            new FormTypeExtension(),
            new FormButtonExtension(),
        ])->getFormFactory();

        $options = ['filter' => $this];

        $cssClass = [];

        if (isset($filter['cssClass']) && '' !== $filter['cssClass']) {
            $cssClass[] = $filter['cssClass'];
        }

        if (!$configuration['skipSession']) {
            if ($this->hasData()) {
                $cssClass[] = 'has-data';
            }
        }

        if (!empty($cssClass)) {
            $options['attr']['class'] = implode(' ', $cssClass);
        }

        if ($filter['asyncFormSubmit']) {
            $options['attr']['data-async'] = 1;

            if ($filter['ajaxList']) {
                $options['attr']['data-list'] = '#huh-list-'.$filter['ajaxList'];
            }
        }

        if (!$configuration['skipAjax'] && $this->requestStack->getCurrentRequest()->isXmlHttpRequest()) {
            $this->container->get('huh.filter.util.filter_ajax')->updateData($this);
            $data = $this->getData();
        }

        if (isset($filter['renderEmpty']) && true === (bool) $filter['renderEmpty']) {
            $data = [];
        }

        $event = System::getContainer()->get('event_dispatcher')->dispatch(
            new FilterFormAdjustOptionsEvent($options, $filter, $this),
            FilterFormAdjustOptionsEvent::class
        );

        $this->builder = $factory->createNamedBuilder($filter['name'], FilterType::class, $data, $event->getOptions());

        $this->mapFormsToData();
    }

    /**
     * Init query builder.
     *
     * @param array $skipElements Array with tl_filter_config_element ids that should be skipped on initQueryBuilder
     */
    public function initQueryBuilder(
        array $skipElements = [],
        $mode = self::QUERY_BUILDER_MODE_DEFAULT,
        bool $doNotChangeExistingQueryBuilder = false
    ): FilterQueryBuilder {
        $queryBuilder = new FilterQueryBuilder($this->container, $this->framework, $this->queryBuilder->getConnection());

        if ($doNotChangeExistingQueryBuilder) {
            $this->doInitQueryBuilder(
                $queryBuilder,
                $skipElements,
                $mode
            );
        } else {
            $this->queryBuilder = $queryBuilder;

            $this->doInitQueryBuilder(
                $this->queryBuilder,
                $skipElements,
                $mode
            );
        }

        return $queryBuilder;
    }

    public function doInitQueryBuilder(
        FilterQueryBuilder $queryBuilder,
        array $skipElements = [],
        $mode = self::QUERY_BUILDER_MODE_DEFAULT
    ): void {
        $queryBuilder->from($this->getFilter()['dataContainer']);

        if (null === $this->getElements()) {
            return;
        }

        $types = $this->container->get('huh.filter.choice.type')->getCachedChoices();

        if (!\is_array($types) || empty($types)) {
            return;
        }

        foreach ($this->getElements() as $element) {
            if (!$element->published) {
                return;
            }

            $initial = ($element->isInitial || in_array($element->type, [
                PublishedType::TYPE,
                SqlType::TYPE,
                SkipParentsType::TYPE,
            ]));

            if (!isset($types[$element->type])
                || in_array($element->id, $skipElements)
                || $mode === static::QUERY_BUILDER_MODE_INITIAL_ONLY && !$initial
                || $mode === static::QUERY_BUILDER_MODE_SKIP_INITIAL && $initial) {
                continue;
            }

            $config = $types[$element->type];
            $class = $config['class'];
            $skip = $queryBuilder->getSkip();

            if (!class_exists($class) || isset($skip[$element->id])) {
                continue;
            }

            /** @var AbstractType $type */
            $type = new $class($this);

            if (!is_subclass_of($type, AbstractType::class)) {
                continue;
            }

            if (!$type::isEnabledForCurrentContext([
                'filterConfigElementModel' => $element,
                'table' => $this->getFilter()['dataContainer'],
            ])) {
                continue;
            }

            $type->buildQuery($queryBuilder, $element);
        }
    }

    /**
     * @param mixed $request The request to handle
     */
    public function handleForm($request = null): ?RedirectResponse
    {
        if (null === $this->getBuilder()) {
            $this->buildForm($this->getData());
        }

        if (null === $this->getBuilder()) {
            return null;
        }

        try {
            $form = $this->getBuilder()->getForm();
        } catch (TransformationFailedException $e) {
            // for instance field changed from single to multiple value, transform old session data will throw an TransformationFailedException -> clear session and build again with empty data
            $this->resetData();
            $this->buildForm($this->getData());
            $form = $this->getBuilder()->getForm();
        }

        $form->handleRequest($request);

        // redirect back to tl_filter_config.action or given referrer
        $url = $this->getUrl() ?: $form->get(FilterType::FILTER_REFERRER_NAME)->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->has(FilterType::FILTER_ID_NAME)) {
                return null;
            }

            // form id must match
            if ((int) $form->get(FilterType::FILTER_ID_NAME)->getData() !== $this->getId()) {
                return null;
            }

            $data = $form->getData();
            $data['f_submitted'] = true;
            $url = $this->utils->url()->removeQueryStringParameterFromUrl($form->getName(), $url ?: null);

            // do not save filter id in session
            $this->setData($this->filter['mergeData'] ? array_merge($this->getData(), $data) : $data);
            $this->getBuilder()->setData($this->getData());

            // allow reset, support different form configuration with same form name
            if ($this->isResetButtonClicked($form)) {
                $this->resetData();
                $this->getBuilder()->setData($this->getData());
                // redirect to referrer page without filter parameters
                $url = $this->utils->url()->removeQueryStringParameterFromUrl($form->getName(),
                    $form->get(FilterType::FILTER_REFERRER_NAME)->getData() ?: null);
            }

            if (parse_url($url, \PHP_URL_HOST) !== parse_url(Environment::get('url'), \PHP_URL_HOST)) {
                throw new \Exception('Invalid redirect url');
            }

            // extract hash if present
            if (str_contains($url, '#')) {
                $urlParts = explode('#', $url);

                $url = implode('#', \array_slice($urlParts, 0, \count($urlParts) - 1));

                $url = $this->utils->url()->addQueryStringParameterToUrl('t='.time(), $url).'#'.$urlParts[\count($urlParts) - 1];
            } else {
                $url = $this->utils->url()->addQueryStringParameterToUrl('t='.time(), $url);
            }

            return new RedirectResponse($url, 303);
        }

        return null;
    }

    public function getId(): ?int
    {
        return $this->filter['id'] ?? null;
    }

    /**
     * @return array|null
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Get a specific element by its value.
     *
     * @param mixed  $value The to search within $key
     * @param string $key   The array key
     *
     * @return FilterConfigElementModel|null
     */
    public function getElementByValue(mixed $value, string $key = 'id'): ?FilterConfigElementModel
    {
        if (null === $this->getElements()) {
            return null;
        }

        if (\is_array($value)) {
            $value = serialize($value);
        }

        foreach ($this->getElements() as $element) {
            if (null === $element->{$key} || (string) $element->{$key} !== (string) $value) {
                continue;
            }

            return $element;
        }

        return null;
    }

    /**
     * @return \Contao\Model\Collection|FilterConfigElementModel[]|null
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @return FormBuilderInterface|null
     */
    public function getBuilder(): ?FormBuilderInterface
    {
        return $this->builder;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @return FilterQueryBuilder|null
     */
    public function getQueryBuilder(): ?FilterQueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * Set the filter data.
     */
    public function setData(array $data = []): void
    {
        $this->session->setData($this->getSessionKey(), $data);
    }

    /**
     * Get the filter data (e.g. form submission data).
     */
    public function getData(): array
    {
        $data = [];

        if ($this->sessionKey) {
            $data = $this->session->getData($this->getSessionKey());
        }

        if (!$this->requestStack->getCurrentRequest()) {
            return $data;
        }

        $currentUrl = strtok($this->requestStack->getCurrentRequest()->getSchemeAndHttpHost().
            $this->requestStack->getCurrentRequest()->getRequestUri(), '?');

        $referrer = strtok($this->requestStack->getCurrentRequest()->headers->get('referer'), '?');

        if ($this->filter['resetFilterInitial']) {
            if (isset($data[FilterType::FILTER_FORM_SUBMITTED]) && true === $data[FilterType::FILTER_FORM_SUBMITTED]) {
                $data[FilterType::FILTER_FORM_SUBMITTED] = false;
                $this->formSubmitted = true;
                $this->setData($data);
            } elseif (false === $this->formSubmitted && false !== $referrer && $currentUrl !== $referrer) {
                // only reset if the visitor comes from another page than the filtered one
                // without this restriction pagination wouldn't work
                $this->resetData();
                $data = [];
            }
        }

        return $data;
    }

    /**
     * Has the filter data (e.g. form submitted?).
     */
    public function hasData(): bool
    {
        return $this->session->hasData($this->getSessionKey());
    }

    /**
     * Reset the filter data.
     */
    public function resetData(): void
    {
        $this->session->reset($this->getSessionKey());
    }

    public function isSubmitted(): bool
    {
        $data = $this->getData();

        return isset($data[FilterType::FILTER_ID_NAME]);
    }

    public function getResetNames(): array
    {
        return $this->resetNames;
    }

    public function addResetName(string $resetName): void
    {
        $this->resetNames[] = $resetName;
    }

    public function setResetNames(array $resetNames): void
    {
        $this->resetNames = $resetNames;
    }

    public function getFramework(): ContaoFramework
    {
        return $this->framework;
    }

    public function addContextualValue($elementId, $values): void
    {
        $this->queryBuilder->addContextualValue($elementId, $values);
    }

    public function getFilterTemplateByName(string $name)
    {
        $config = $this->container->getParameter('huh.filter');

        if (!isset($config['filter']['templates'])) {
            return $this->container->get(TwigTemplateLocator::class)->getTemplatePath($name);
        }

        $templates = $config['filter']['templates'];

        foreach ($templates as $template) {
            if ($template['name'] === $name) {
                return $template['template'];
            }
        }

        return $this->container->get(TwigTemplateLocator::class)->getTemplatePath($name);
    }

    /**
     * Get the redirect url based on current filter action.
     *
     * @return string
     *
     * @since 1.0.0-beta128.2 Url is absolute
     */
    public function getUrl(): string
    {
        $filter = $this->getFilter();

        if (!empty($filter['parentFilter'])) {
            $parentFilter = $this->framework->getAdapter(FilterConfigModel::class)->findById($filter['parentFilter'])->row();

            if (!empty($parentFilter)) {
                $filter['filterFormAction'] = $parentFilter['filterFormAction'];
                $filter['name'] = $parentFilter['name'];
                $filter['dataContainer'] = $parentFilter['dataContainer'];
                $filter['method'] = $parentFilter['method'];
                $filter['mergeData'] = $parentFilter['mergeData'];
            }
        }

        if (empty($filter['filterFormAction'])) {
            return '';
        }

        $action = $this->insertTagParser->replace($filter['filterFormAction']);

        return Environment::get('url').'/'.urldecode($action);
    }

    /**
     * Get the form action url to internal filter_frontend_submit action.
     */
    public function getAction()
    {
        $router = $this->container->get('router');

        $filter = $this->getFilter();

        if (!isset($filter['id'])) {
            return null;
        }

        if ($filter['asyncFormSubmit']) {
            return $router->generate('filter_frontend_ajax_submit', [
                'id' => $filter['id'],
                '_locale' => $this->requestStack->getCurrentRequest()->getLocale(),
            ]);
        }

        return $router->generate('filter_frontend_submit', ['id' => $filter['id']]);
    }

    /**
     * Get the preselection action url.
     *
     * @param array $data Preselection data
     *
     * @return string|null
     */
    public function getPreselectAction(array $data = [], bool $absoluteUrl = false): ?string
    {
        /** @var RouterInterface $router */
        $router = $this->container->get('router');

        $filter = $this->getFilter();

        if (!isset($filter['id'])) {
            return null;
        }

        return $router->generate(
            'filter_frontend_preselect',
            ['id' => $filter['id'], 'data' => array_filter($data)],
            $absoluteUrl ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    /**
     * Handle current request or the given one.
     */
    public function handleRequest(Request $request = null): void
    {
        if (null === $request) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        }

        if ($request->query->has(FilterType::FILTER_RESET_URL_PARAMETER_NAME)) {
            $this->resetData();
            $target = $this->utils->url()->removeQueryStringParameterFromUrl(
                FilterType::FILTER_RESET_URL_PARAMETER_NAME,
                $request->getUri()
            );
            Controller::redirect($target);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    protected function isResetButtonClicked(FormInterface $form): bool
    {
        if ($form->getClickedButton() === null
            || !in_array($form->getClickedButton()->getName(), $this->getResetNames(), true))
        {
            return $this->isResetButtonClickedFromRequest();
        }
        return true;
    }

    protected function isResetButtonClickedFromRequest(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $data = in_array($request->getMethod(), ['GET', 'HEAD']) ? Input::get($this->getFilter()['name']) : Input::post($this->getFilter()['name']);

        return isset($data['reset']);
    }

    /**
     * Maps the data of the current forms and update builder data.
     */
    protected function mapFormsToData()
    {
        $data = [];

        try {
            // in case of form configuration did change (e.g. choice from single to multiple value), we need to reset form data
            $forms = $this->builder->getForm();
        } catch (TransformationFailedException $e) {
            $this->resetData();
            $this->builder->setData($this->getData());
            $forms = $this->builder->getForm();
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        /*
         * @var FormInterface
         */
        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            // Write-back is disabled if the form is not synchronized (transformation failed),
            // if the form was not submitted and if the form is disabled (modification not allowed)
            if (null !== $propertyPath && $config->getMapped() && $form->isSynchronized() && !$form->isDisabled()) {
                // If the field is of type DateTime and the data is the same skip the update to
                // keep the original object hash
                if ($form->getData() instanceof \DateTime && $form->getData() === $propertyAccessor->getValue($data,
                        $propertyPath)) {
                    continue;
                }

                // If the data is identical to the value in $data, we are
                // dealing with a reference
                if (!\is_object($data) || !$config->getByReference() || $form->getData() !== $propertyAccessor->getValue($data,
                        $propertyPath)) {
                    $propertyAccessor->setValue($data, $propertyPath, $form->getData());
                }
            }
        }

        $this->builder->setData($data);
    }
}
