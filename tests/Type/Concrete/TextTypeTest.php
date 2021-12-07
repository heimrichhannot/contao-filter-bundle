<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Type\Concrete;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPart;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Type\AbstractFilterType;
use HeimrichHannot\FilterBundle\Type\Concrete\TextType;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Copyright (c) 2021 Heimrich & Hannot GmbH.
 *
 * @license LGPL-3.0-or-later
 */
class TextTypeTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function createTestInstance(array $parameters = [], $mockBuilder = false)
    {
        $translator = $parameters['translator'] ?? $this->createMock(TranslatorInterface::class);
        $processor = $parameters['processor'] ?? $this->createMock(FilterQueryPartProcessor::class);
        $collection = $parameters['collection'] ?? $this->createMock(FilterQueryPartCollection::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnCallback(function ($argument) use ($translator) {
           switch ($argument) {
               case 'translator':
                   return $translator;
           };
           return null;
        });

        if ($mockBuilder) {
            $instance = $this->getMockBuilder(TextType::class)
                ->setConstructorArgs([$container, $processor, $collection])
                ->setMethods(['getOptions', 'buildForm'])
                ->getMock();
        } else {
            $instance = new TextType($container, $processor, $collection);
        }

        return $instance;
    }

    public function testGetType()
    {
        $instance = $this->createTestInstance();
        $this->assertSame('text_type', $instance->getType());
    }

    public function testGetPalette()
    {
        $instance = $this->createTestInstance();
        $prepend = '{test_prepend_legend},testPrependField;';
        $append = '{test_append_legend},testAppendField;';
        $expected = '{config_legend},field,operator,submitOnInput;{visualization_legend},addPlaceholder,addDefaultValue,customLabel,hideLabel,inputGroup;';

        $this->assertSame($expected, $instance->getPalette('', ''));
        $this->assertSame($prepend.$expected, $instance->getPalette($prepend, ''));
        $this->assertSame($expected.$append, $instance->getPalette('', $append));
        $this->assertSame($prepend.$expected.$append, $instance->getPalette($prepend, $append));
    }

    public function testGetInitialPalette()
    {
        $instance = $this->createTestInstance();
        $prepend = '{test_prepend_legend},testPrependField;';
        $append = '{test_append_legend},testAppendField;';
        $expected = '{config_legend},field,operator,initialValueType;';

        $this->assertSame($expected, $instance->getInitialPalette('', ''));
        $this->assertSame($prepend.$expected, $instance->getInitialPalette($prepend, ''));
        $this->assertSame($expected.$append, $instance->getInitialPalette('', $append));
        $this->assertSame($prepend.$expected.$append, $instance->getInitialPalette($prepend, $append));
    }

    public function testGetInitialValueTypes()
    {
        $instance = $this->createTestInstance();
        $typesArray = [];
        $this->assertSame([], $instance->getInitialValueTypes($typesArray));

        $typesArray = [AbstractFilterType::VALUE_TYPE_ARRAY];
        $this->assertSame($typesArray, $instance->getInitialValueTypes($typesArray));

        $typesArray = [AbstractFilterType::VALUE_TYPE_CONTEXTUAL];
        $this->assertSame($typesArray, $instance->getInitialValueTypes($typesArray));

        $typesArray = [
            AbstractFilterType::VALUE_TYPE_ARRAY,
            AbstractFilterType::VALUE_TYPE_CONTEXTUAL,
        ];
        $this->assertSame($typesArray, $instance->getInitialValueTypes($typesArray));
    }

    public function testGetOptions()
    {
        $translator = new Translator('de');
        $instance = $this->createTestInstance(['translator' => $translator]);

        $filterTypeContext = $this->createMock(FilterTypeContext::class);

        $filterConfigElement = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'submitOnInput' => true,
        ]);
        $filterTypeContext->method('getElementConfig')->willReturn($filterConfigElement);

        $filterConfig = $this->createMock(FilterConfigModel::class);
        $filterConfig->method('row')->willReturn(['asyncFormSubmit' => true]);
        $filterTypeContext->method('getFilterConfig')->willReturn($filterConfig);

        $optionsArray = $instance->getOptions($filterTypeContext);

        $this->assertArrayHasKey('data-submit-on-input', $optionsArray['attr']);
        $this->assertArrayHasKey('data-threshold', $optionsArray['attr']);
        $this->assertArrayHasKey('data-debounce', $optionsArray['attr']);

        $this->assertSame('1', $optionsArray['attr']['data-submit-on-input']);
        $this->assertSame('0', $optionsArray['attr']['data-threshold']);
        $this->assertSame('0', $optionsArray['attr']['data-debounce']);

        $config = $this->createMock(FilterConfigModel::class);
        $config->method('row')->willReturn(['asyncFormSubmit' => false]);
        $filterContext = $this->createMock(FilterTypeContext::class);
        $filterContext->method('getFilterConfig')->willReturn($config);
        $options = $instance->getOptions($filterContext);

        $this->assertArrayNotHasKey('data-submit-on-input', $options['attr']);
        $this->assertArrayNotHasKey('data-threshold', $options['attr']);
        $this->assertArrayNotHasKey('data-debounce', $options['attr']);
    }

    public function testBuildQuery()
    {
        $filterContext = $this->createMock(FilterTypeContext::class);

        $queryPartCollection = new FilterQueryPartCollection();

        $queryPartProcessor = $this->createMock(FilterQueryPartProcessor::class);

        $queryPartProcessor->method('composeQueryPart')->willReturn($this->createMock(FilterQueryPart::class));

        $instance = $this->createTestInstance(['processor' => $queryPartProcessor, 'collection' => $queryPartCollection]);
        $this->assertEmpty($queryPartCollection->getParts());

        $instance->buildQuery($filterContext);
        $this->assertCount(0, $queryPartCollection->getParts());

        $context = new FilterTypeContext();
        $context->setValue('text');
//        $instance

        $elementConfig = $this->createMock(FilterConfigElementModel::class);
        $context->setElementConfig($elementConfig);
        $instance->buildQuery($context);
        $this->assertCount(1, $queryPartCollection->getParts());
    }

    public function testBuildForm()
    {
        $context = $this->createMock(FilterTypeContext::class);
        $elementConfig = $this->createMock(FilterConfigElementModel::class);
        $elementConfig->method('getElementName')->willReturn('TextType');
        $context->method('getElementConfig')->willReturn($elementConfig);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formBuilder = new FormBuilder($elementConfig->getElementName(), null, $eventDispatcher, $formFactory);
        $context->method('getFormBuilder')->willReturn($formBuilder);

        $this->assertSame(0, $context->getFormBuilder()->count());
        $instance = $this->createTestInstance();
        $instance->buildQuery($context);

        // TODO: why is this not working?
//        $this->assertSame(1, $context->getFormBuilder()->count());
    }
}
