<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Model\TagModel;
use Contao\Controller;
use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\CategoriesBundle\Model\CategoryModel;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Config\FileLocator;
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
    public function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        unset($GLOBALS['TL_DCA']['tl_test'], $GLOBALS['TL_FFL']);

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
        $instance = new FieldOptionsChoice($framework);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice', $instance);
    }

    /**
     * Tests the field options choices based on tag field without tag manager service.
     */
    public function testCollectTagOptionsWithTranslation()
    {
        $translator = new Translator('de');
        $translator->getCatalogue()->add(['message.tag.tagA' => 'Translated tag A']);
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $tagModelA = $this->mockAdapter(['getName', 'getValue']);
        $tagModelA->method('getName')->willReturn('message.tag.tagA');
        $tagModelA->method('getValue')->willReturn(1);

        $tagModelB = $this->mockAdapter(['getName', 'getValue']);
        $tagModelB->method('getName')->willReturn('Tag B');
        $tagModelB->method('getValue')->willReturn(2);

        $tagModelAdapter = $this->mockAdapter(['findByCriteria']);
        $tagModelAdapter->method('findByCriteria')->willReturn([$tagModelA, $tagModelB]);

        $framework = $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
                TagModel::class => $tagModelAdapter,
            ]
        );

        $defaultManagerAdapter = $this->mockAdapter(['findMultiple']);
        $defaultManagerAdapter->method('findMultiple')->willReturn([$tagModelA, $tagModelB]);

        $tagManagerRegistry = $this->mockAdapter(['get']);
        $tagManagerRegistry->method('get')->willReturn($defaultManagerAdapter);

        $this->container->set('codefog_tags.manager_registry', $tagManagerRegistry);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label' => ['foo', 'bar'],
                    'inputType' => 'cfgTags',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'tagsManager' => 'tag_manager.test',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertSame(['Translated tag A' => 1, 'Tag B' => 2], $choices);
    }

    /**
     * Tests the field options choices based on tag field without tags.
     */
    public function testCollectTagOptionsWithoutTags()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $tagModelAdapter = $this->mockAdapter(['findByCriteria']);
        $tagModelAdapter->method('findByCriteria')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
                TagModel::class => $tagModelAdapter,
            ]
        );

        $defaultManagerAdapter = $this->mockAdapter(['findMultiple']);
        $defaultManagerAdapter->method('findMultiple')->willReturn(null);

        $tagManagerRegistry = $this->mockAdapter(['get']);
        $tagManagerRegistry->method('get')->willReturn($defaultManagerAdapter);

        $this->container->set('codefog_tags.manager_registry', $tagManagerRegistry);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label' => ['foo', 'bar'],
                    'inputType' => 'cfgTags',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'tagsManager' => 'tag_manager.test',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on tag field without tag manager service.
     */
    public function testCollectTagOptionsWithoutTagManager()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $tagModelAdapter = $this->mockAdapter(['findByCriteria']);
        $tagModelAdapter->method('findByCriteria')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
                TagModel::class => $tagModelAdapter,
            ]
        );

        $tagManagerRegistry = $this->mockAdapter(['get']);
        $tagManagerRegistry->method('get')->willReturn(new DefaultManager($framework, 'tl_test', 'test'));

        $this->container->set('codefog_tags.manager_registry', $tagManagerRegistry);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label' => ['foo', 'bar'],
                    'inputType' => 'cfgTags',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'tagsManager' => 'tag_manager.test',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on tag field without codefog_tags.manager_registry service.
     */
    public function testCollectTagOptionsWithoutTagsManagerRegistryService()
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
                    'label' => ['foo', 'bar'],
                    'inputType' => 'cfgTags',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'tagsManager' => 'tag_manager.test',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on tag field without tagsManager in eval set.
     */
    public function testCollectTagOptionsWithoutTagManagerEval()
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
                    'label' => ['foo', 'bar'],
                    'inputType' => 'cfgTags',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on category field with categories and translation.
     */
    public function testCollectCategoryOptionsWithTranslation()
    {
        $translator = new Translator('de');
        $translator->getCatalogue()->add(['message.categoryA.frontendTitle' => 'Frontend Title Category A']);
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $categoryA = $this->mockClassWithProperties(
            CategoryModel::class,
            [
                'id' => 1,
                'frontendTitle' => 'message.categoryA.frontendTitle',
                'title' => 'Category A',
            ]
        );

        $categoryB = $this->mockClassWithProperties(
            CategoryModel::class,
            [
                'id' => 2,
                'frontendTitle' => 'Frontend Title Category B',
                'title' => 'Category B',
            ]
        );

        $categoryAdapter = $this->mockAdapter(['findByCategoryFieldAndTable']);
        $categoryAdapter->method('findByCategoryFieldAndTable')->willReturn(
            [
                $categoryA,
                $categoryB,
            ]
        );
        $this->container->set('huh.categories.manager', $categoryAdapter);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label' => ['foo', 'bar'],
                    'inputType' => 'select',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'isCategoryField' => true,
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertSame(['Frontend Title Category A' => 1, 'Frontend Title Category B' => 2], $choices);
    }

    /**
     * Tests the field options choices based on category field without existing categories.
     */
    public function testCollectCategoryOptionsWithoutCategories()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $categoryAdapter = $this->mockAdapter(['findByCategoryFieldAndTable']);
        $categoryAdapter->method('findByCategoryFieldAndTable')->willReturn(null);
        $this->container->set('huh.categories.manager', $categoryAdapter);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'label' => ['foo', 'bar'],
                    'inputType' => 'select',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'isCategoryField' => true,
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on category field without existing category manager.
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
                    'label' => ['foo', 'bar'],
                    'inputType' => 'select',
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                        'isCategoryField' => true,
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field from `options_callback`.
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
                    'label' => ['foo', 'bar'],
                    'inputType' => 'select',
                    'options_callback' => function () {
                        return ['optionA', 'optionB'];
                    },
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertSame(['optionA' => 'optionA', 'optionB' => 'optionB'], $choices);
    }

    /**
     * Tests the field options choices based on given dca field from `options` with reference.
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
                    'label' => ['foo', 'bar'],
                    'inputType' => 'select',
                    'options' => [
                        'optionA',
                        'optionB',
                    ],
                    'options_callback' => null,
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                    ],
                    'reference' => &$GLOBALS['TL_LANG']['tl_test']['test']['reference'],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertSame(['translated option A' => 'optionA', 'translated option B' => 'optionB'], $choices);
    }

    /**
     * Tests the field options choices based on given dca field from `options`.
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
                    'label' => ['foo', 'bar'],
                    'inputType' => 'select',
                    'options' => [
                        'optionA',
                        'optionB',
                    ],
                    'options_callback' => null,
                    'eval' => [
                        'submitOnChange' => false,
                        'allowHtml' => false,
                        'rte' => false,
                        'preserveTags' => false,
                        'isAssociative' => false,
                        'includeBlankOption' => false,
                        'sql' => '',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertSame(['optionA' => 'optionA', 'optionB' => 'optionB'], $choices);
    }

    /**
     * Tests the field options choices based on given dca field without frontend widget $GLOBALS['TL_FFL'] class.
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
        $GLOBALS['BE_FFL']['checkboxWizard'] = 'NonExistingFormSelectInputClass';

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'inputType' => 'select',
                    'options' => [
                        'optionA',
                        'optionB',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field without frontend widget $GLOBALS['TL_FFL'].
     */
    public function testCollectDcaOptionsFromWidgetWithoutExistingFrontendWidget()
    {
        $translator = new Translator('de');
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        $this->container->set('huh.utils.container', new ContainerUtil($framework, $this->createMock(FileLocator::class)));

        $requestStack = new RequestStack();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('_contao_referer_id', 'foobar');
        $requestStack->push($request);

        $this->container->set('request_stack', $requestStack);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [
            'fields' => [
                'test' => [
                    'inputType' => 'select',
                    'options' => [
                        'optionA',
                        'optionB',
                    ],
                ],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field.
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
                'test' => [],
            ],
        ];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices based on given dca field without existing dca field configuration.
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

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'field' => 'test',
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices for custom options.
     */
    public function testCollectCustomOptions()
    {
        $translator = new Translator('de');
        $translator->getCatalogue('de')->add(
            [
                'message.customOption1' => 'My custom option 1 label',
                'message.customOption2' => 'My custom option 2 label',
            ]
        );
        $this->container->set('translator', $translator);
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $GLOBALS['TL_DCA']['tl_test'] = [];

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'customOptions' => true,
                'options' => [
                    ['value' => 'customOption1', 'label' => 'message.customOption1'],
                    ['value' => 'customOption2', 'label' => 'message.customOption2'],
                ],
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertSame(
            [
                'My custom option 1 label' => 'customOption1',
                'My custom option 2 label' => 'customOption2',
            ],
            $choices
        );
    }

    /**
     * Tests the field options choices without custom options.
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

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'customOptions' => true,
                'options' => null,
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices for invalid custom options.
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

        $elementModel = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'customOptions' => true,
                'options' => [
                    ['customOption1' => 'message.customOption1'],
                    ['customOption2' => 'message.customOption2'],
                ],
            ]
        );

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => $elementModel,
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices with controller adapter.
     */
    public function testCollectWithController()
    {
        $this->container->set('kernel', $this->kernel);

        $controllerAdapter = $this->mockAdapter(['loadDataContainer']);
        $controllerAdapter->method('loadDataContainer')->willReturn(null);

        $framework = $this->mockContaoFramework([Controller::class => $controllerAdapter]);

        System::setContainer($this->container);

        $context = [
            'filter' => ['dataContainer' => 'tl_test'],
            'element' => [],
        ];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without controller adapter.
     */
    public function testCollectWithoutController()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['filter' => [], 'element' => []];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without element context.
     */
    public function testCollectWithoutElementContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['filter' => []];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without filter context.
     */
    public function testCollectWithoutFilterContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['element' => []];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the field options choices without filter and element context.
     */
    public function testCollectWithoutFilterAndElementsContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = [];

        $instance = new FieldOptionsChoice($framework);
        $choices = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'Fixtures';
    }

    /**
     * Mocks the plugin loader.
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expects
     * @param array                                              $plugins
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
