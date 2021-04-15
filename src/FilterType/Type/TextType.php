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
        return $prependPalette.'{config_legend},field,operator;{visualization_legend},addPlaceholder,customLabel,hideLabel;{expert_legend},cssClass;'.$appendPalette;
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
        ];

        return array_values(array_diff(parent::getOperators(), $remove));
    }
}
