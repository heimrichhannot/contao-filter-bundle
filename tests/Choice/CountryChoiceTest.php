<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\CountryChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class CountryChoiceTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $config;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        $plugin = new Plugin();

        $containerBuilder = new \Contao\ManagerPlugin\Config\ContainerBuilder($this->mockPluginLoader($this->never()), []);

        $config = $plugin->getExtensionConfig('huh_filter', [[]], $containerBuilder);
        $this->config['filter'] = $config['huh']['filter'];

        // required within Contao\Widget::getAttributesFromDca()
        if (!\function_exists('array_is_assoc')) {
            include_once __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->container->set('kernel', $this->kernel);
        $this->container->setParameter('huh.filter', $this->config);

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $instance = new CountryChoice($framework);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Choice\CountryChoice', $instance);
    }

    /**
     * Tests the country collection for associative dca field options.
     */
    public function testCollectAssociativeDcaFieldOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $GLOBALS['TL_FFL']['select'] = 'Contao\SelectMenu';

        $GLOBALS['TL_DCA']['test']['fields']['test'] = [
            'label' => 'test',
            'inputType' => 'select',
            'options' => ['DE' => 'Deutschland Test', 'AT' => 'Österreich Test'],
            'options_callback' => null,
            'eval' => [
                'submitOnChange' => false,
                'allowHtml' => false,
                'rte' => null,
                'preserveTags' => false,
                'isAssociative' => false,
                'includeBlankOption' => false,
                'sql' => null,
            ],
        ];

        $elementProperties = [
            'type' => 'choice',
            'field' => 'test',
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            [
                'id' => 1,
                'dataContainer' => 'test',
            ],
        ];

        System::setContainer($this->container);

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['Deutschland Test' => 'DE', 'Österreich Test' => 'AT'], $choices);
    }

    /**
     * Tests the country collection for dca field options.
     */
    public function testCollectDcaFieldOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $GLOBALS['TL_FFL']['select'] = 'Contao\SelectMenu';

        $GLOBALS['TL_DCA']['test']['fields']['test'] = [
            'label' => 'test',
            'inputType' => 'select',
            'options' => ['DE', 'AT'],
            'options_callback' => null,
            'eval' => [
                'submitOnChange' => false,
                'allowHtml' => false,
                'rte' => null,
                'preserveTags' => false,
                'isAssociative' => false,
                'includeBlankOption' => false,
                'sql' => null,
            ],
        ];

        $elementProperties = [
            'type' => 'choice',
            'field' => 'test',
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            [
                'id' => 1,
                'dataContainer' => 'test',
            ],
        ];

        System::setContainer($this->container);

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['Germany' => 'DE', 'Austria' => 'AT'], $choices);
    }

    /**
     * Tests the country collection for custom options.
     */
    public function testCollectAndTranslateCustomOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $translator = new Translator('en');
        $translator->getCatalogue()->set('test.label.DE', 'Deutschland Test');
        $translator->getCatalogue()->set('test.label.AT', 'Österreich Test');

        $this->container->set('translator', $translator);

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customOptions' => true,
            'options' => serialize(
                [
                    [
                        'value' => 'DE',
                        'label' => 'test.label.DE',
                    ],
                    [
                        'value' => 'AT',
                        'label' => 'test.label.AT',
                    ],
                ]
            ),
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            ['id' => 1],
        ];

        System::setContainer($this->container);

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['Deutschland Test' => 'DE', 'Österreich Test' => 'AT'], $choices);
    }

    /**
     * Tests the country collection for custom options.
     */
    public function testCollectCustomOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customOptions' => true,
            'options' => serialize(
                [
                    [
                        'value' => 'DE',
                        'label' => 'DE',
                    ],
                    [
                        'value' => 'AT',
                        'label' => 'AT',
                    ],
                ]
            ),
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            ['id' => 1],
        ];

        System::setContainer($this->container);

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['Germany' => 'DE', 'Austria' => 'AT'], $choices);
    }

    /**
     * Tests the country collection for custom country options.
     */
    public function testCollectCustomCountries()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customCountries' => true,
            'countries' => serialize(['DE', 'AT']),
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            ['id' => 1],
        ];

        System::setContainer($this->container);

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['Austria' => 'AT', 'Germany' => 'DE'], $choices);
    }

    /**
     * Tests the country collection for custom countries without options.
     */
    public function testCollectCustomCountriesWithoutOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customCountries' => true,
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            ['id' => 1],
        ];

        System::setContainer($this->container);

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the country collection without element.
     */
    public function testCollectWithoutElement()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, []);

        $context = [
            $element,
            ['id' => 1, 'dataContainer' => 'test'],
        ];

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the country collection with invalid context.
     */
    public function testCollectWithInvalidContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['foo' => []];

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the country collection without filter and element.
     */
    public function testCollectWithoutFilterAndElement()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = [];

        $instance = new CountryChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures';
    }

    /**
     * Mocks the plugin loader.
     *
     * @param Invocation $expects
     * @param array      $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(Invocation $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
