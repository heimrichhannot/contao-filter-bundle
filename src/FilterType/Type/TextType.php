<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Doctrine\ORM\EntityManagerInterface;
use HeimrichHannot\FilterBundle\Filter\Filter;
use HeimrichHannot\FilterBundle\FilterType\AbstractFilterType;
use HeimrichHannot\FilterBundle\FilterType\FilterTypeContext;
use HeimrichHannot\FilterBundle\FilterType\InitialFilterTypeInterface;

class TextType extends AbstractFilterType implements InitialFilterTypeInterface
{
    const TYPE = 'future_text';
    const GROUP = 'text';

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var Filter
     */
    protected $filter;

    public function __construct(Filter $filter, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->filter = $filter;
        $this->initialize();
    }

    public static function getType(): string
    {
        return static::TYPE;
    }

    public function buildQuery(FilterTypeContext $filterTypeContext)
    {
        try {
            foreach ($filterTypeContext->getIterator() as $param) {
                $this->filter->setParameter($param->key(), $param->current());
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->em->getFilters()->enable('huh_filter');
    }

    public function buildForm(FilterTypeContext $filterTypeContext)
    {
    }

    public function getPalette(): string
    {
        if ($this->getContext()->isInitial()) {
            return '{initial_legend},isInitial;{general_legend},title,type;{config_legend},field';
        }

        return '{initial_legend},isInitial;{general_legend},title,type;{config_legend},field';
    }

//    public function preparePalette($filterTypeContext): void
//    {
    ////        '{general_legend},type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue,submitOnInput;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;',
//        $paletteManipulator = PaletteManipulator::create();
//
//        $paletteManipulator->addField(
//            ($filterTypeContext->isInitial() ? 'initialType' : 'type'),
//            'general_legend',
//            PaletteManipulator::POSITION_APPEND);
//
//        $paletteManipulator->applyToPalette(static::TYPE, 'tl_filter_config_element');
//    }

    public function getInitialPalette(FilterTypeContext $filterTypeContext): string
    {
        return '{initial_legend},isInitial;{general_legend},title;{config_legend},field';
    }

    private function initialize(): void
    {
        $this->setContext(new FilterTypeContext());
        $this->setGroup(static::GROUP);
    }
}
