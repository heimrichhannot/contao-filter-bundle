<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\LanguageChoice;
use HeimrichHannot\FilterBundle\Choice\LocaleChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Translation\Translator;

class LocaleChoiceTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var ContaoKernel
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

        $this->kernel = $this->createMock(ContaoKernel::class);
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
        $instance = new LocaleChoice($framework);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Choice\LocaleChoice', $instance);
    }

    /**
     * Tests the locale collection for associative dca field options.
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
            'options' => ['de_DE' => 'Deutsch Test', 'en' => 'Englisch Test'],
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

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['Deutsch Test' => 'de_DE', 'Englisch Test' => 'en'], $choices);
    }

    /**
     * Tests the locale collection for associative dca field options without existing field class.
     */
    public function testCollectAssociativeDcaFieldWithNonExistingFieldClassOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $GLOBALS['TL_FFL']['select'] = '_NonExistingNameSpace\NonExisingClass';

        $GLOBALS['TL_DCA']['test']['fields']['test'] = [
            'label' => 'test',
            'inputType' => 'select',
            'options' => ['de_DE' => 'Deutsch Test', 'en' => 'Englisch Test'],
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

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the locale collection for dca field options.
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
            'options' => ['de_DE', 'en'],
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

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['German (Germany)' => 'de_DE', 'English' => 'en'], $choices);
    }

    /**
     * Tests the locale collection for custom options.
     */
    public function testCollectAndTranslateCustomOptions()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $translator = new Translator('en');
        $translator->getCatalogue()->set('test.label.de_DE', 'German (Germany) Test');
        $translator->getCatalogue()->set('test.label.en', 'English Test');

        $this->container->set('translator', $translator);

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customOptions' => true,
            'options' => serialize(
                [
                    [
                        'value' => 'de_DE',
                        'label' => 'test.label.de_DE',
                    ],
                    [
                        'value' => 'en',
                        'label' => 'test.label.en',
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

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['German (Germany) Test' => 'de_DE', 'English Test' => 'en'], $choices);
    }

    /**
     * Tests the locale collection for custom options.
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
                        'value' => 'de_DE',
                        'label' => 'de_DE',
                    ],
                    [
                        'value' => 'en',
                        'label' => 'en',
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

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['German (Germany)' => 'de_DE', 'English' => 'en'], $choices);
    }

    /**
     * Tests the locales collection for custom locale options without locales set.
     */
    public function testCollectCustomNonExistingLocales()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customLocales' => true,
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            ['id' => 1],
        ];

        System::setContainer($this->container);

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the locales collection for custom locale options.
     */
    public function testCollectCustomLocales()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $elementProperties = [
            'type' => 'choice',
            'customLocales' => true,
            'locales' => serialize(['de_DE', 'en']),
        ];

        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, $elementProperties);

        $context = [
            $element,
            ['id' => 1],
        ];

        System::setContainer($this->container);

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        $this->assertNotEmpty($choices);
        $this->assertArraySubset(['English' => 'en', 'German (Germany)' => 'de_DE'], $choices);
    }

    /**
     * Tests the locales collection without element.
     */
    public function testCollectWithoutElement()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        $this->container->set('translator', new Translator('en'));

        System::setContainer($this->container);

        $context = [
            [],
            ['id' => 1, 'dataContainer' => 'test'],
        ];

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the locales collection with invalid context.
     */
    public function testCollectWithInvalidContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['foo' => []];

        $instance = new LocaleChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the locales collection without filter and element.
     */
    public function testCollectWithoutFilterAndElement()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = [];

        $instance = new LanguageChoice($framework);
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
     * @param InvokedRecorder $expects
     * @param array                                                 $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(InvokedRecorder $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
