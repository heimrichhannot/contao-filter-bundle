<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

use HeimrichHannot\FilterBundle\Filter\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\Filter\FilterQueryPartProcessor;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractFilterType implements FilterTypeInterface
{
    const GROUP_DEFAULT = 'miscellaneous';

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
     * @var FilterTypeContext
     */
    private $context;

    /**
     * @var string
     */
    private $group = '';

    public function __construct(
        FilterQueryPartProcessor $filterQueryPartProcessor,
        FilterQueryPartCollection $filterQueryPartCollection,
        TranslatorInterface $translator
    ) {
        $this->initialize();
        $this->filterQueryPartProcessor = $filterQueryPartProcessor;
        $this->filterQueryPartCollection = $filterQueryPartCollection;
        $this->translator = $translator;
    }

    public function getContext(): FilterTypeContext
    {
        if (!isset($this->context)) {
            $this->setDefaultContext();
        }

        return $this->context;
    }

    public function setContext(FilterTypeContext $context)
    {
        $this->context = $context;
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
        return DatabaseUtil::OPERATORS;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        $this->filterQueryPartCollection->addPart($this->filterQueryPartProcessor->composeQueryPart($filterTypeContext));
    }

    public function getOptions(FilterTypeContext $context): array
    {
        $options = [];

        if ($context->getPlaceholder()) {
            $options['attr']['placeholder'] = $this->translator->trans($context->getPlaceholder(), ['%label%' => $this->translator->trans($options['label']) ?: $context->getTitle()]);
        }

        if ($context->getCssClass()) {
            $options['attr']['class'] = $context->getCssClass();
        }

        $options['label'] = $context->getLabel() ?: $context->getTitle();

        // sr-only style for non-bootstrap projects is shipped within the filter_form_* templates
        if (true === $context->isLabelHidden()) {
            $options['label_attr'] = ['class' => 'sr-only'];
        }

        // always label for screen readers
        $options['attr']['aria-label'] = $this->translator->trans($context->getLabel() ?: $context->getTitle());

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

    private function setDefaultContext()
    {
        $this->context = new FilterTypeContext();
    }
}
