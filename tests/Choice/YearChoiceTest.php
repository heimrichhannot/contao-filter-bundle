<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\YearChoice;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpKernel\Kernel;

class YearChoiceTest extends ContaoTestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(YearChoice::class, $this->getYearChoiceInstance());
    }

    public function testCollect()
    {
        $yearChoice = $this->getYearChoiceInstance();
        $this->assertEmpty($yearChoice->getChoices());
        $this->assertEmpty($yearChoice->getChoices([]));

        $filterMock = ['dataContainer' => 'tl_news'];
        $parentElementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'isInitial' => '1',
            'initialValueArray' => serialize([]),
        ]);
        $context = [
            'filter' => $filterMock,
            'parentElement' => $parentElementMock,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'isInitial' => '1',
            'initialValueArray' => serialize([['value' => '']]),
        ]);
        $context = [
            'filter' => $filterMock,
            'parentElement' => $parentElementMock,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'isInitial' => '1',
            'initialValueArray' => serialize([['value' => '1'], ['value' => '2']]),
        ]);
        $context = [
            'filter' => $filterMock,
            'parentElement' => $parentElementMock,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'isInitial' => '1',
            'initialValueArray' => serialize([['value' => '2'], ['value' => '3']]),
        ]);
        $context = [
            'filter' => $filterMock,
            'parentElement' => $parentElementMock,
        ];
        $this->assertSame([
            2018 => '2018',
            2017 => '2017',
            2016 => '2016',
        ], $yearChoice->getChoices($context));

        $parentElementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'value' => '1',
        ]);
        $context = [
            'filter' => $filterMock,
            'parentElement' => $parentElementMock,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'value' => '2',
        ]);
        $context = [
            'filter' => $filterMock,
            'parentElement' => $parentElementMock,
        ];
        $this->assertSame([
            2018 => '2018',
            2017 => '2017',
            2016 => '2016',
        ], $yearChoice->getChoices($context));
    }

    public function getYearChoiceInstance()
    {
        $container = $this->mockContainer();
        $kernelMock = $this->createMock(Kernel::class);
        $kernelMock->method('getCacheDir')->willReturn($this->getTempDir());
        $kernelMock->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernelMock);
        System::setContainer($container);

        $framework = $this->mockContaoFramework();
        $modelUtil = $this->createMock(ModelUtil::class);
        $modelUtil->method('findModelInstancesBy')->willReturnCallback(function ($table, $field, $value) {
            switch ($value[0]) {
                default:
                case '1':
                    return null;
                case '2':
                    return [
                        $this->mockClassWithProperties(Model::class, ['date' => 1529916218]), //2018
                        $this->mockClassWithProperties(Model::class, ['date' => 1502496000]), //2017
                        $this->mockClassWithProperties(Model::class, ['date' => 1486252800]), //2017
                        $this->mockClassWithProperties(Model::class, ['date' => 1462406400]), //2016
                    ];
            }
        });
        $yearChoice = new YearChoice($framework, $modelUtil);

        return $yearChoice;
    }
}
