<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

class ChoiceType extends AbstractFilterType
{
    const TYPE = 'choice_type';

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildForm($filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();

        $builder->add($filterTypeContext->getName(), SymfonyChoiceType::class, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field,operator;{visualization_legend},addPlaceholder,customLabel,hideLabel;{expert_legend},cssClass;'.$appendPalette;
    }

    public function getOperators(): array
    {
        //remove this operators from the DatabaseUtil::OPERATORS array
        $remove = [
            DatabaseUtil::OPERATOR_LIKE,
            DatabaseUtil::OPERATOR_UNLIKE,
            DatabaseUtil::OPERATOR_GREATER,
            DatabaseUtil::OPERATOR_GREATER_EQUAL,
            DatabaseUtil::OPERATOR_LOWER,
            DatabaseUtil::OPERATOR_LOWER_EQUAL,
        ];

        return array_values(array_diff(parent::getOperators(), $remove));
    }
}
