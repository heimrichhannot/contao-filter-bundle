<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\YearChoice;
use HeimrichHannot\FilterBundle\Filter\AbstractType;
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
        $parentElements = [];
        $parentElements[] = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'isInitial' => '1',
            'initialValueType' => AbstractType::VALUE_TYPE_ARRAY,
            'initialValueArray' => serialize([]),
        ]);
        $elementMock = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
        ]);
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = new \stdClass();
        $parentElementMock->id = 5;
        $parentElementMock->isInitial = '1';
        $parentElementMock->initialValueType = AbstractType::VALUE_TYPE_ARRAY;
        $parentElementMock->initialValueArray = serialize([['value' => '']]);
        $parentElementMock->field = 'pid';
        $parentElements = [$parentElementMock];
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = new \stdClass();
        $parentElementMock->id = 5;
        $parentElementMock->isInitial = '1';
        $parentElementMock->initialValueType = AbstractType::VALUE_TYPE_ARRAY;
        $parentElementMock->initialValueArray = serialize([['value' => '1'], ['value' => '2']]);
        $parentElementMock->field = 'pid';
        $parentElements = [$parentElementMock];
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = new \stdClass();
        $parentElementMock->id = 5;
        $parentElementMock->isInitial = '1';
        $parentElementMock->initialValueType = AbstractType::VALUE_TYPE_ARRAY;
        $parentElementMock->initialValueArray = serialize([['value' => '2'], ['value' => '3']]);
        $parentElementMock->field = 'pid';
        $parentElements = [$parentElementMock];
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
        ];
        $this->assertSame([
            2018 => '2018',
            2017 => '2017',
            2016 => '2016',
        ], $yearChoice->getChoices($context));

        $parentElementMock = new \stdClass();
        $parentElementMock->id = 5;
        $parentElementMock->isInitial = '1';
        $parentElementMock->initialValueType = AbstractType::VALUE_TYPE_SCALAR;
        $parentElementMock->initialValue = 1;
        $parentElementMock->field = 'pid';
        $parentElements = [$parentElementMock];
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
        ];
        $this->assertEmpty($yearChoice->getChoices($context));

        $parentElementMock = new \stdClass();
        $parentElementMock->id = 5;
        $parentElementMock->isInitial = '1';
        $parentElementMock->initialValueType = AbstractType::VALUE_TYPE_SCALAR;
        $parentElementMock->initialValue = 2;
        $parentElementMock->field = 'pid';
        $parentElements = [$parentElementMock];
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
        ];
        $this->assertSame([
            2018 => '2018',
            2017 => '2017',
            2016 => '2016',
        ], $yearChoice->getChoices($context));

        $parentElementMock = new \stdClass();
        $parentElementMock->id = 5;
        $parentElementMock->isInitial = '1';
        $parentElementMock->initialValueType = AbstractType::VALUE_TYPE_SCALAR;
        $parentElementMock->initialValue = 2;
        $parentElementMock->field = 'pid';
        $parentElements = [$parentElementMock];
        $context = [
            'filter' => $filterMock,
            'element' => $elementMock,
            'elements' => $parentElements,
            'latest' => true,
        ];
        $this->assertSame([2018 => '2018'], $yearChoice->getChoices($context));
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
        $modelUtil->method('findModelInstancesBy')->willReturnCallback(function ($table, $fields, $values, $options) {
            if (empty($values)) {
                $values[0] = substr($fields[0], strpos($fields[0], '(') + 1, 1);
            }
            $return = null;

            switch ($values[0]) {
                default:
                case '1':
                    return null;

                case '2':
                    $return = [
                        $this->mockClassWithProperties(Model::class, ['date' => 1529916218]), //2018
                        $this->mockClassWithProperties(Model::class, ['date' => 1502496000]), //2017
                        $this->mockClassWithProperties(Model::class, ['date' => 1486252800]), //2017
                        $this->mockClassWithProperties(Model::class, ['date' => 1462406400]), //2016
                    ];
            }

            if (isset($options['limit']) && 1 === $options['limit']) {
                return [$return[0]];
            }

            return $return;
        });
        $yearChoice = new YearChoice($framework, $modelUtil);

        return $yearChoice;
    }
}
