<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\Controller;
use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class FieldOptionsChoiceTest extends ContaoTestCase
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

        unset($GLOBALS['TL_DCA']['tl_test']);
        unset($GLOBALS['TL_FFL']);

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        $plugin = new Plugin();

        $containerBuilder = new \Contao\ManagerPlugin\Config\ContainerBuilder($this->mockPluginLoader($this->never()), []);

        $config                 = $plugin->getExtensionConfig('huh_filter', [[]], $containerBuilder);
        $this->config['filter'] = $config['huh']['filter'];

        // required within Contao\Widget::getAttributesFromDca()
        if (!\function_exists('array_is_assoc')) {
            include_once __DIR__ . '/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
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
        $instance  = new FieldOptionsChoice($framework);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice', $instance);
    }

    /**
     * Tests the field options choices based on category field without existing category manager
     */
    public function testCollectCategoryOptionsWithoutCategoryManagerService()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label'     => ['foo', 'bar'],
                    'inputType' => 'select',
                    'eval'      => [
                        'submitOnChange'     => false,
                        'allowHtml'          => false,
                        'rte'                => false,
                        'preserveTags'       => false,
                        'isAssociative'      => false,
                        'includeBlankOption' => false,
                        'sql'                => '',
                        'isCategoryField'    => true,
                    ],
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field from `options_callback`
     */
    public function testCollectDcaOptionsFromWidgetOptionsCallback()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_FFL'] = [
            'select' => 'FormSelectMenu',
        ];

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label'            => ['foo', 'bar'],
                    'inputType'        => 'select',
                    'options_callback' => function () {
                        return ['optionA', 'optionB'];
                    },
                    'eval'             => [
                        'submitOnChange'     => false,
                        'allowHtml'          => false,
                        'rte'                => false,
                        'preserveTags'       => false,
                        'isAssociative'      => false,
                        'includeBlankOption' => false,
                        'sql'                => '',
                    ],
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertEquals(['optionA' => 'optionA', 'optionB' => 'optionB'], $choices);
    }

    /**
     * Tests the field options choices based on given dca field from `options` with reference
     */
    public function testCollectDcaOptionsFromWidgetOptionsWithReference()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_FFL'] = [
            'select' => 'FormSelectMenu',
        ];

        $GLOBALS['TL_LANG']['tl_test']['test']['reference']['optionA'] = 'translated option A';
        $GLOBALS['TL_LANG']['tl_test']['test']['reference']['optionB'] = 'translated option B';

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label'            => ['foo', 'bar'],
                    'inputType'        => 'select',
                    'options'          => [
                        'optionA',
                        'optionB'
                    ],
                    'options_callback' => null,
                    'eval'             => [
                        'submitOnChange'     => false,
                        'allowHtml'          => false,
                        'rte'                => false,
                        'preserveTags'       => false,
                        'isAssociative'      => false,
                        'includeBlankOption' => false,
                        'sql'                => '',
                    ],
                    'reference'        => &$GLOBALS['TL_LANG']['tl_test']['test']['reference']
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertEquals(['translated option A' => 'optionA', 'translated option B' => 'optionB'], $choices);
    }

    /**
     * Tests the field options choices based on given dca field from `options`
     */
    public function testCollectDcaOptionsFromWidgetOptions()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_FFL'] = [
            'select' => 'FormSelectMenu',
        ];

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label'            => ['foo', 'bar'],
                    'inputType'        => 'select',
                    'options'          => [
                        'optionA',
                        'optionB'
                    ],
                    'options_callback' => null,
                    'eval'             => [
                        'submitOnChange'     => false,
                        'allowHtml'          => false,
                        'rte'                => false,
                        'preserveTags'       => false,
                        'isAssociative'      => false,
                        'includeBlankOption' => false,
                        'sql'                => '',
                    ],
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertEquals(['optionA' => 'optionA', 'optionB' => 'optionB'], $choices);
    }

    /**
     * Tests the field options choices based on given dca field without frontend widget $GLOBALS['TL_FFL'] class
     */
    public function testCollectDcaOptionsFromWidgetWithoutExistingFrontendWidgetClass()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        $GLOBALS['TL_FFL']['select'] = 'NonExistingFormSelectInputClass';

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'inputType' => 'select',
                    'options'   => [
                        'optionA',
                        'optionB'
                    ]
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field without frontend widget $GLOBALS['TL_FFL']
     */
    public function testCollectDcaOptionsFromWidgetWithoutExistingFrontendWidget()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'inputType' => 'select',
                    'options'   => [
                        'optionA',
                        'optionB'
                    ]
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field
     */
    public function testCollectDcaOptionsWithoutInputType()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                ]
            ]
        ];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field without existing dca field configuration
     */
    public function testCollectDcaOptionsForNonExistingField()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'field' => 'test'
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices for custom options
     */
    public function testCollectCustomOptions()
    {
        $translator = new Translator('de');
        $translator->getCatalogue('de')->add(
            [
                'message.customOption1' => 'My custom option 1 label',
                'message.customOption2' => 'My custom option 2 label'
            ]
        );
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'customOptions' => true,
            'options'       => [
                ['value' => 'customOption1', 'label' => 'message.customOption1'],
                ['value' => 'customOption2', 'label' => 'message.customOption2']
            ]
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertEquals(
            [
                'My custom option 1 label' => 'customOption1',
                'My custom option 2 label' => 'customOption2'
            ], $choices);
    }

    /**
     * Tests the field options choices without custom options
     */
    public function testCollectCustomOptionsWithoutOptions()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'customOptions' => true,
            'options'       => null
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices for invalid custom options
     */
    public function testCollectCustomOptionsOnInvalidOptions()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [];

        $elementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'customOptions' => true,
            'options'       => [
                ['customOption1' => 'message.customOption1'],
                ['customOption2' => 'message.customOption2']
            ]
        ]);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => $elementModel
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices with controller adapter
     */
    public function testCollectWithController()
    {
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $context = [
            'filter'  => ['dataContainer' => 'tl_test'],
            'element' => []
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without controller adapter
     */
    public function testCollectWithoutController()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['filter' => [], 'element' => []];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without element context
     */
    public function testCollectWithoutElementContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['filter' => []];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }


    /**
     * Tests the field options choices without filter context
     */
    public function testCollectWithoutFilterContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['element' => []];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without filter and element context
     */
    public function testCollectWithoutFilterAndElementsContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = [];

        $instance = new FieldOptionsChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures';
    }

    /**
     * Mocks the plugin loader.
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expects
     * @param array $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(\PHPUnit_Framework_MockObject_Matcher_InvokedCount $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
