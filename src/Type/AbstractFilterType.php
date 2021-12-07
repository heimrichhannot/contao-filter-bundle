<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Type;

use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\AbstractServiceSubscriber;
use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractFilterType extends AbstractServiceSubscriber implements FilterTypeInterface
{
    const GROUP_DEFAULT = 'miscellaneous';

    const VALUE_TYPE_SCALAR = 'scalar';
    const VALUE_TYPE_ARRAY = 'array';
    const VALUE_TYPE_CONTEXTUAL = 'contextual';
    const VALUE_TYPE_LATEST = 'latest';

    const VALUE_TYPES = [
        self::VALUE_TYPE_SCALAR,
        self::VALUE_TYPE_ARRAY,
        self::VALUE_TYPE_CONTEXTUAL,
    ];

    /**
     * @var FilterQueryPartProcessor
     */
    protected $filterQueryPartProcessor;

    /**
     * @var FilterQueryPartCollection
     */
    protected $filterQueryPartCollection;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    private $group = '';

    /** @var ContainerInterface */
    protected $container;

    public function __construct(
        ContainerInterface $container,
        FilterQueryPartProcessor $filterQueryPartProcessor,
        FilterQueryPartCollection $filterQueryPartCollection
    ) {
        $this->initialize();
        $this->filterQueryPartProcessor = $filterQueryPartProcessor;
        $this->filterQueryPartCollection = $filterQueryPartCollection;
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.$appendPalette;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getOperators(): array
    {
        return [
            DatabaseUtil::OPERATOR_LIKE,
            DatabaseUtil::OPERATOR_UNLIKE,
            DatabaseUtil::OPERATOR_EQUAL,
            DatabaseUtil::OPERATOR_UNEQUAL,
            DatabaseUtil::OPERATOR_LOWER,
            DatabaseUtil::OPERATOR_LOWER_EQUAL,
            DatabaseUtil::OPERATOR_GREATER,
            DatabaseUtil::OPERATOR_GREATER_EQUAL,
            DatabaseUtil::OPERATOR_IN,
            DatabaseUtil::OPERATOR_NOT_IN,
            DatabaseUtil::OPERATOR_IS_NULL,
            DatabaseUtil::OPERATOR_IS_NOT_NULL,
            DatabaseUtil::OPERATOR_REGEXP,
            DatabaseUtil::OPERATOR_NOT_REGEXP,
        ];
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        $this->filterQueryPartCollection->addPart($this->filterQueryPartProcessor->composeQueryPart($filterTypeContext));
    }

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $elementConfig = $filterTypeContext->getElementConfig();
        $options = [];
        $options['label'] = $elementConfig->customLabel ? $elementConfig->label : $elementConfig->title;

        // sr-only style for non-bootstrap projects is shipped within the filter_form_* templates
        if (true === (bool) $elementConfig->hideLabel) {
            $options['label_attr'] = ['class' => 'sr-only'];
        }
        // always label for screen readers
        $options['attr']['aria-label'] = $this->translator->trans($elementConfig->customLabel ? $elementConfig->label : $elementConfig->title);

        if ((bool) $elementConfig->addPlaceholder) {
            $options['attr']['placeholder'] = $this->translator->trans($elementConfig->placeholder, ['%label%' => $this->translator->trans($options['label']) ?: $elementConfig->title]);
        }

        if (!empty($elementConfig->cssClass)) {
            $options['attr']['class'] = $elementConfig->cssClass;
        }

        if ((bool) $elementConfig->addDefaultValue) {
            $options['data'] = $elementConfig->defaultValue;
        }

        if ((bool) $elementConfig->inputGroup && !empty($elementConfig->inputGroupPrepend)) {
            $prepend = $elementConfig->inputGroupPrepend;

            if ($this->translator->getCatalogue()->has($prepend)) {
                $prepend = $this->translator->trans($prepend, ['%label%' => $this->translator->trans($options['label']) ?: $elementConfig->title]);
            }

            $options['input_group_prepend'] = $prepend;
        }

        if ((bool) $elementConfig->inputGroup && !empty($elementConfig->inputGroupAppend)) {
            $append = $elementConfig->inputGroupAppend;

            if ($this->translator->getCatalogue()->has($append)) {
                $append = $this->translator->trans($append, ['%label%' => $this->translator->trans($options['label']) ?: $elementConfig->title]);
            }

            $options['input_group_append'] = $append;
        }

        $options['block_name'] = $elementConfig->getElementName();

        return $options;
    }

    protected function initialize(): void
    {
        if (empty($this->group) && !\defined('static::GROUP')) {
            $this->setGroup(static::GROUP_DEFAULT);
        } else {
            $this->setGroup(static::GROUP);
        }
    }

    public static function getSubscribedServices()
    {
        return [
            'translator' => 'translator',
        ];
    }


}
