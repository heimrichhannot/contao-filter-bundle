<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType;

use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
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

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $options = [];
        $options['label'] = $filterTypeContext->isCustomLabel() ? $filterTypeContext->getLabel() : $filterTypeContext->getTitle();

        // sr-only style for non-bootstrap projects is shipped within the filter_form_* templates
        if (true === $filterTypeContext->isLabelHidden()) {
            $options['label_attr'] = ['class' => 'sr-only'];
        }
        // always label for screen readers
        $options['attr']['aria-label'] = $this->translator->trans($filterTypeContext->isCustomLabel() ? $filterTypeContext->getLabel() : $filterTypeContext->getTitle());

        if ($filterTypeContext->getPlaceholder()) {
            $options['attr']['placeholder'] = $this->translator->trans($filterTypeContext->getPlaceholder(), ['%label%' => $this->translator->trans($options['label']) ?: $filterTypeContext->getTitle()]);
        }

        if ($filterTypeContext->getCssClass()) {
            $options['attr']['class'] = $filterTypeContext->getCssClass();
        }

        if ($filterTypeContext->getDefaultValue()) {
            $options['data'] = $filterTypeContext->getDefaultValue();
        }

        if ($filterTypeContext->hasInputGroup()) {
            if ('' !== $filterTypeContext->getInputGroupPrepend()) {
                $prepend = $filterTypeContext->getInputGroupPrepend();

                if ($this->translator->getCatalogue()->has($prepend)) {
                    $prepend = $this->translator->trans($prepend, ['%label%' => $this->translator->trans($options['label']) ?: $filterTypeContext->getTitle()]);
                }

                $options['input_group_prepend'] = $prepend;
            }

            if ('' !== $filterTypeContext->getInputGroupAppend()) {
                $append = $filterTypeContext->getInputGroupAppend();

                if ($this->translator->getCatalogue()->has($append)) {
                    $append = $this->translator->trans($append, ['%label%' => $this->translator->trans($options['label']) ?: $filterTypeContext->getTitle()]);
                }

                $options['input_group_append'] = $append;
            }
        }

        $options['block_name'] = $filterTypeContext->getName();

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
}
