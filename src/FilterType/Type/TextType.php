<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\FilterBundle\FilterType\InitialFilterTypeInterface;
use HeimrichHannot\FilterBundle\FilterType\PlaceholderFilterTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType as SymfonyTextType;

class TextType extends AbstractFilterType implements InitialFilterTypeInterface, PlaceholderFilterTypeInterface
{
    const TYPE = 'text_type';
    const GROUP = 'text';

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        $this->filterQueryPartCollection->addPart($this->filterQueryPartProcessor->composeQueryPart($filterTypeContext));
    }

    public function buildForm(FilterTypeContext $filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();
        $builder->add($filterTypeContext->getElementConfig()->getElementName(), SymfonyTextType::class, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,submitOnInput;{visualization_legend},addPlaceholder,addDefaultValue,customLabel,hideLabel,inputGroup;'.$appendPalette;
    }

    public function getInitialPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator;'.$appendPalette;
    }

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $options = parent::getOptions($filterTypeContext);

        $elementConfig = $filterTypeContext->getElementConfig();

        if ((bool) $elementConfig->submitOnInput && (bool) $filterTypeContext->getParent()->row()['asyncFormSubmit']) {
            $options['attr']['data-submit-on-input'] = '1';
            $options['attr']['data-threshold'] = $elementConfig->threshold ?: '0';
            $options['attr']['data-debounce'] = $elementConfig->debounce ?: '0';
        }

        return $options;
    }

    public function getPlaceholders(): array
    {
        return [];
    }
}
