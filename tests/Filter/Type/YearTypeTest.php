<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\YearChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\YearType;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Translation\TranslatorInterface;

class YearTypeTest extends ContaoTestCase
{
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(YearType::class, $this->getYearTypeInstance());
    }

    public function getYearTypeInstance($filterConfig = null)
    {
        $dateUtilMock = $this->createMock(DateUtil::class);
        $modelUtilMock = $this->createMock(ModelUtil::class);
        $yearChoiceMock = $this->createMock(YearChoice::class);
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $container = $this->mockContainer();
        $container->set('huh.utils.date', $dateUtilMock);
        $container->set('huh.utils.model', $modelUtilMock);
        $container->set('huh.filter.choice.year', $yearChoiceMock);
        $container->set('translator', $translatorMock);
        System::setContainer($container);

        if (!$filterConfig) {
            $filterConfig = $this->createMock(FilterConfig::class);
        }

        return new YearType($filterConfig);
    }
}
