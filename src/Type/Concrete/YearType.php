<?php

namespace HeimrichHannot\FilterBundle\Type\Concrete;

use HeimrichHannot\FilterBundle\Type\AbstractFilterType;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use HeimrichHannot\FilterBundle\Type\InitialFilterTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

class YearType extends AbstractFilterType implements InitialFilterTypeInterface
{
    const TYPE = 'year_type';

    public function buildForm(FilterTypeContext $filterTypeContext)
    {
        $builder = $filterTypeContext->getFormBuilder();
        $builder->add($filterTypeContext->getElementConfig()->getElementName(), SymfonyChoiceType::class, $this->getOptions($filterTypeContext));
    }

    public function getInitialPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.$appendPalette;
    }

    public function getInitialValueTypes(array $types): array
    {
        return [];
    }

    public static function getType(): string
    {
        return static::TYPE;
    }
}