<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use Symfony\Component\Form\Extension\Core\Type\ButtonType as SymfonyButtonType;
use Symfony\Component\Form\Extension\Core\Type\ResetType as SymfonyResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType as SymfonySubmitType;

class ButtonType extends AbstractFilterType
{
    const TYPE = 'button_type';
    const GROUP = 'button';

    const BUTTON_TYPE_BUTTON = 'button';
    const BUTTON_TYPE_RESET = 'reset';
    const BUTTON_TYPE_SUBMIT = 'submit';

    const BUTTON_TYPES = [
        self::BUTTON_TYPE_BUTTON,
        self::BUTTON_TYPE_RESET,
        self::BUTTON_TYPE_SUBMIT,
    ];

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext): string
    {
        return '';
    }

    public function buildForm(FilterTypeContext $filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();

        switch ($filterTypeContext->getButtonType()) {
            case static::BUTTON_TYPE_RESET:
                $symfonyButton = SymfonyResetType::class;

                break;

            case static::BUTTON_TYPE_SUBMIT:
                $symfonyButton = SymfonySubmitType::class;

                break;

            default:
                $symfonyButton = SymfonyButtonType::class;

                break;
        }

        $builder->add($filterTypeContext->getName(), $symfonyButton, $this->getOptions($filterTypeContext));
    }

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},buttonType;{visualization_legend},customLabel;'.$appendPalette;
    }

    public function getOptions(FilterTypeContext $filterTypeContext): array
    {
        $options = parent::getOptions($filterTypeContext);

//        if ($filterTypeContext->getButtonType() === static::BUTTON_TYPE_RESET) {
//            $options['attr']['onclick'] = 'this.form.submit()';
//        }

        return $options;
    }
}
