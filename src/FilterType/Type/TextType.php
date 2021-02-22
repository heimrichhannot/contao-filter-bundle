<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\FilterType\Type;

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

    public function getPalette(FilterTypeContext $context): string
    {
        if ($this->getContext()->isInitial()) {
            return $this->getInitialPalette();
        }

        return '{initial_legend},isInitial;{general_legend},title,type;{config_legend},field;{expert_legend},cssClass;{publish_legend},published;';
    }

    public function getInitialPalette(): string
    {
        return '{initial_legend},isInitial;{general_legend},title,type;{config_legend},field;';
    }

    private function initialize(): void
    {
        $this->setGroup(static::GROUP);
    }
}
