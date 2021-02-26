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

    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field;{expert_legend},cssClass;'.$appendPalette;
    }

    public function getInitialPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},field;'.$appendPalette;
    }
}
