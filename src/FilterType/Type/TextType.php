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
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\Extension\Core\Type\TextType as SymfonyTextType;

class TextType extends AbstractFilterType implements InitialFilterTypeInterface
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

        $builder->add($filterTypeContext->getName(), SymfonyTextType::class, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator,submitOnInput;{visualization_legend},addPlaceholder,addDefaultValue,customLabel,hideLabel,inputGroup;'.$appendPalette;
    }

    public function getInitialPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator;'.$appendPalette;
    }

    public function getOperators(): array
    {
        //remove this operators from the DatabaseUtil::OPERATORS array
        $remove = [
            DatabaseUtil::OPERATOR_GREATER,
            DatabaseUtil::OPERATOR_GREATER_EQUAL,
            DatabaseUtil::OPERATOR_LOWER,
            DatabaseUtil::OPERATOR_LOWER_EQUAL,
            DatabaseUtil::OPERATOR_IN,
            DatabaseUtil::OPERATOR_NOT_IN,
            DatabaseUtil::OPERATOR_IS_NULL,
            DatabaseUtil::OPERATOR_IS_NOT_NULL,
            DatabaseUtil::OPERATOR_IS_EMPTY,
            DatabaseUtil::OPERATOR_IS_NOT_EMPTY,
        ];

        return array_values(array_diff(parent::getOperators(), $remove));
    }

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $options = parent::getOptions($filterTypeContext);

        if ($filterTypeContext->isSubmitOnInput() && (bool) $filterTypeContext->getParent()->row()['asyncFormSubmit']) {
            $options['attr']['data-submit-on-input'] = '1';
            $options['attr']['data-threshold'] = $filterTypeContext->getThreshold();
            $options['attr']['data-debounce'] = $filterTypeContext->getDebounce();
        }

        return $options;
    }
}
