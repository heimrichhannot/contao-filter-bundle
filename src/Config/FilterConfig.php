<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Config;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\InsertTags;
use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
use HeimrichHannot\FilterBundle\Form\Extension\FormButtonExtension;
use HeimrichHannot\FilterBundle\Form\Extension\FormTypeExtension;
use HeimrichHannot\FilterBundle\Form\FilterType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

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

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var FilterSession
     */
    protected $session;

    /**
     * @var string
     */
    protected $sessionKey;

    /**
     * @var array
     */
    protected $resetNames;

    /**
     * @var array|null
     */
    protected $filter;

    /**
     * @var \Contao\Model\Collection|FilterConfigElementModel[]|null
     */
    protected $elements;

    /**
     * @var FormBuilderInterface|null
     */
    protected $builder;

    /**
     * @var FilterQueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var bool
     */
    protected $formSubmitted = false;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param FilterSession            $session
     */
    public function __construct(
        ContainerInterface $container,
        ContaoFrameworkInterface $framework,
        FilterSession $session,
        Connection $connection
    ) {
        $this->framework = $framework;
        $this->session = $session;
        $this->container = $container;
        $this->queryBuilder = new FilterQueryBuilder($this->container, $this->framework, $connection);
    }

    /**
     * Init the filter based on its model.
     *
     * @param string                                                   $sessionKey
     * @param array                                                    $filter
     * @param \Contao\Model\Collection|FilterConfigElementModel[]|null $elements
     */
    public function init(string $sessionKey, array $filter, $elements = null)
    {
        $this->filter = $filter;
        $this->sessionKey = $sessionKey;
        $this->elements = $elements;
    }

    /**
     * Build the form.
     *
     * @param array $data
     */
    public function buildForm(array $data = [])
    {
        if (null === $this->filter) {
            return;
        }

        $factory = Forms::createFormFactoryBuilder()->addTypeExtensions([
            new FormTypeExtension(),
            new FormButtonExtension(),
        ])->getFormFactory();

        $options = ['filter' => $this];

        $cssClass = [];

        if (isset($this->filter['cssClass']) && '' !== $this->filter['cssClass']) {
            $cssClass[] = $this->filter['cssClass'];
        }

        if ($this->hasData()) {
            $cssClass[] = 'has-data';
        }

        if (!empty($cssClass)) {
            $options['attr']['class'] = implode(' ', $cssClass);
        }

        if ($this->getFilter()['asyncFormSubmit']) {
            $options['attr']['data-async'] = 1;
            $options['attr']['data-list'] = '#huh-list-'.$this->getFilter()['ajaxList'];
            $this->container->get('huh.filter.util.filter_ajax')->updateData($this);
            $data = $this->getData();
        }

        if (isset($this->filter['renderEmpty']) && true === (bool) $this->filter['renderEmpty']) {
            $data = [];
        }

        $this->builder = $factory->createNamedBuilder($this->filter['name'], FilterType::class, $data, $options);

        $this->mapFormsToData();
    }

    /**
     * Init query builder.
     *
     * @param array $skipElements Array with tl_filter_config_element ids that should be skipped on initQueryBuilder
     */
    public function initQueryBuilder(array $skipElements = [], $mode = self::QUERY_BUILDER_MODE_DEFAULT, bool $doNotChangeExistingQueryBuilder = false)
    {
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

    public function doInitQueryBuilder(FilterQueryBuilder $queryBuilder, array $skipElements = [], $mode = self::QUERY_BUILDER_MODE_DEFAULT)
    {
        $queryBuilder->from($this->getFilter()['dataContainer']);

        if (null === $this->getElements()) {
            return;
        }

        $types = $this->container->get('huh.filter.choice.type')->getCachedChoices();

        if (!\is_array($types) || empty($types)) {
            return;
        }

        foreach ($this->getElements() as $element) {
            if (!isset($types[$element->type]) || \in_array($element->id, $skipElements) ||
                $mode === static::QUERY_BUILDER_MODE_INITIAL_ONLY && !$element->isInitial ||
                $mode === static::QUERY_BUILDER_MODE_SKIP_INITIAL && $element->isInitial) {
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

            $type->buildQuery($queryBuilder, $element);
        }
    }

    /**
     * @param mixed $request The request to handle
     *
     * @return RedirectResponse|null
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
            $url = $this->container->get('huh.utils.url')->removeQueryString([$form->getName()], $url ?: null);

            // do not save filter id in session
            $this->setData($this->filter['mergeData'] ? array_merge($this->getData(), $data) : $data);

            // allow reset, support different form configuration with same form name
            if (null !== $form->getClickedButton() && \in_array($form->getClickedButton()->getName(),
                    $this->getResetNames(), true)) {
                $this->resetData();
                // redirect to referrer page without filter parameters
                $url = $this->container->get('huh.utils.url')->removeQueryString([$form->getName()],
                    $form->get(FilterType::FILTER_REFERRER_NAME)->getData() ?: null);
            }

            if (parse_url($url, PHP_URL_HOST) !== parse_url(Environment::get('url'), PHP_URL_HOST)) {
                throw new \Exception('Invalid redirect url');
            }

            return new RedirectResponse($this->container->get('huh.utils.url')->addQueryString('t='.time(), $url), 303);
        }

        return null;
    }

    /**
     * @return int|null
     */
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
    public function getElementByValue($value, $key = 'id')
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
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * @return FilterQueryBuilder|null
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Set the filter data.
     *
     * @param array $data
     */
    public function setData(array $data = [])
    {
        $this->session->setData($this->getSessionKey(), $data);
    }

    /**
     * Get the filter data (e.g. form submission data).
     *
     * @return array
     */
    public function getData(): array
    {
        $data = $this->session->getData($this->getSessionKey());

        if ($this->filter['resetFilterInitial']) {
            if (isset($data[FilterType::FILTER_FORM_SUBMITTED]) && true === $data[FilterType::FILTER_FORM_SUBMITTED]) {
                $data[FilterType::FILTER_FORM_SUBMITTED] = false;
                $this->formSubmitted = true;
                $this->setData($data);
            } elseif (false === $this->formSubmitted) {
                $this->resetData();
                $data = [];
            }
        }

        return $data;
    }

    /**
     * Has the filter data (e.g. form submitted?).
     *
     * @return bool
     */
    public function hasData(): bool
    {
        return $this->session->hasData($this->getSessionKey());
    }

    /**
     * Reset the filter data.
     */
    public function resetData()
    {
        $this->session->reset($this->getSessionKey());
    }

    /**
     * @return bool
     */
    public function isSubmitted(): bool
    {
        $data = $this->getData();

        return isset($data[FilterType::FILTER_ID_NAME]);
    }

    /**
     * @return array
     */
    public function getResetNames(): array
    {
        return !\is_array($this->resetNames) ? [$this->resetNames] : $this->resetNames;
    }

    /**
     * @param string $resetName
     */
    public function addResetName(string $resetName)
    {
        $this->resetNames[] = $resetName;
    }

    /**
     * @param array $resetName
     */
    public function setResetNames(array $resetNames)
    {
        $this->resetName = $resetNames;
    }

    /**
     * @return ContaoFrameworkInterface
     */
    public function getFramework(): ContaoFrameworkInterface
    {
        return $this->framework;
    }

    public function addContextualValue($elementId, $values)
    {
        $this->queryBuilder->addContextualValue($elementId, $values);
    }

    public function getFilterTemplateByName(string $name)
    {
        $config = $this->container->getParameter('huh.filter');

        if (!isset($config['filter']['templates'])) {
            return $this->container->get('huh.utils.template')->getTemplate($name);
        }

        $templates = $config['filter']['templates'];

        foreach ($templates as $template) {
            if ($template['name'] === $name) {
                return $template['template'];
            }
        }

        return $this->container->get('huh.utils.template')->getTemplate($name);
    }

    /**
     * Get the redirect url based on current filter action.
     *
     * @since 1.0.0-beta128.2 Url is absolute
     *
     * @return string
     */
    public function getUrl()
    {
        $filter = $this->getFilter();

        if (!empty($filter['parentFilter'])) {
            $parentFilter = $this->framework->getAdapter(FilterConfigModel::class)->findById($filter['parentFilter'])->row();

            if (!empty($parentFilter)) {
                $filter['action'] = $parentFilter['action'];
                $filter['name'] = $parentFilter['name'];
                $filter['dataContainer'] = $parentFilter['dataContainer'];
                $filter['method'] = $parentFilter['method'];
                $filter['mergeData'] = $parentFilter['mergeData'];
            }
        }

        if (!isset($filter['action']) || empty($filter['action'])) {
            return '';
        }

        /**
         * @var InsertTags
         */
        $insertTagAdapter = $this->framework->createInstance(InsertTags::class);

        // while unit testing, the mock object cant be instantiated
        if (null === $insertTagAdapter) {
            $insertTagAdapter = $this->framework->getAdapter(InsertTags::class);
        }

        return Environment::get('url').'/'.urldecode($insertTagAdapter->replace($filter['action']));
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
            return $router->generate('filter_frontend_ajax_submit', ['id' => $filter['id']]);
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
    public function getPreselectAction(array $data = [])
    {
        $router = $this->container->get('router');

        $filter = $this->getFilter();

        if (!isset($filter['id'])) {
            return null;
        }

        return $router->generate('filter_frontend_preselect', ['id' => $filter['id'], 'data' => $data]);
    }

    /**
     * Handle current request or the given one.
     *
     * @param Request|null $request
     */
    public function handleRequest(Request $request = null)
    {
        if (null === $request) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        }

        if ($request->query->has(FilterType::FILTER_RESET_URL_PARAMETER_NAME)) {
            $this->resetData();
            Controller::redirect($this->container->get('huh.utils.url')->removeQueryString([FilterType::FILTER_RESET_URL_PARAMETER_NAME],
                $request->getUri()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
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
