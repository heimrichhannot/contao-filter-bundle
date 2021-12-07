<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\FilterQuery;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Types\Types;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPart;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Test\ModelMockTrait;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FilterQueryPartTest extends ContaoTestCase
{
    use ModelMockTrait;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var \Contao\CoreBundle\Framework\ContaoFrameworkInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $framework;

    protected function setUp()
    {
        parent::setUp();

        $this->framework = $this->mockContaoFramework();
        $this->container = $this->mockContainer();
    }

    public function createTestInstance(array $parameters = [], $mockBuilder = false)
    {
        $filterTypeContext = $parameters['context'] ?? $this->createMock(FilterTypeContext::class);

        if ($mockBuilder) {
            $instance = $this->getMockBuilder(FilterQueryPart::class)
                ->setConstructorArgs([$filterTypeContext])
                ->getMock();
        } else {
            $instance = new FilterQueryPart($filterTypeContext);
        }

        return $instance;
    }

    public function testGetterSetter()
    {
        System::setContainer($this->container);

        $context = new FilterTypeContext();
        $filterConfigModel = $this->mockModelObject(FilterConfigModel::class, ['dataContainer' => 'tl_news']);
        $context->setFilterConfig($filterConfigModel);

        $elementConfigProps = [
            'id' => 70,
            'type' => 'choice',
            'field' => 'content',
            'operator' => 'unequal',
            'isInitial' => false,
        ];
        $elementConfigModel = $this->mockModelObject(FilterConfigElementModel::class, $elementConfigProps);
        $elementConfigModel->method('getElementName')->willReturn('choice_70');
        $context->setElementConfig($elementConfigModel);
        $context->setValueType(Types::INTEGER);
        $context->setValue(7);

        $instance = $this->createTestInstance(['context' => $context]);
        $this->assertSame('choice_70', $instance->getName());
        $this->assertSame(70, $instance->getFilterElementId());
        $this->assertSame('unequal', $instance->getOperator());
        $this->assertSame('tl_news.content', $instance->getField());
        $this->assertSame(':content_70', $instance->getWildcard());
        $this->assertFalse($instance->isInitial());
        $this->assertSame(7, $instance->getValue());
        $this->assertSame(Types::INTEGER, $instance->getValueType());
        $this->assertFalse($instance->isDisabled());

        $elementConfigInitialProps = [
            'id' => 70,
            'type' => 'text',
            'field' => 'teaser',
            'operator' => 'equal',
            'isInitial' => true,
            'initialValue' => 'text',
            'initialValueType' => Types::STRING,
            'isInitialOverridable' => false,
        ];
        $elementConfigModel = $this->mockModelObject(FilterConfigElementModel::class, $elementConfigInitialProps);
        $elementConfigModel->method('getElementName')->willReturn('text_70');
        $context->setElementConfig($elementConfigModel);
        $instance = $this->createTestInstance(['context' => $context]);

        $this->assertSame('text_70', $instance->getName());
        $this->assertSame(70, $instance->getFilterElementId());
        $this->assertSame('equal', $instance->getOperator());
        $this->assertSame('tl_news.teaser', $instance->getField());
        $this->assertSame(':teaser_70', $instance->getWildcard());
        $this->assertTrue($instance->isInitial());
        $this->assertSame('text', $instance->getInitialValue());
        $this->assertSame(Types::STRING, $instance->getInitialValueType());
        $this->assertSame('text', $instance->getValue());
        $this->assertFalse($instance->isOverridable());
        $this->assertFalse($instance->isDisabled());
    }
}
