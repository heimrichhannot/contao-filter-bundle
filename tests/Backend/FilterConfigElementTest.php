<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Backend;

use Contao\Controller;
use Contao\DataContainer;
use Contao\FormSelectMenu;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Backend\FilterConfigElement;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Registry\FilterRegistry;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Translation\Translator;

class FilterConfigElementTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        unset($GLOBALS['TL_DCA']['tl_filter_config_element']);
    }

    /**
     * Tests modifyPalette() without existing tl_filter_config_element model.
     */
    public function testModifyPaletteWithoutFilterConfigElementModel()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->modifyPalette($this->getDataContainerMock()));
    }

    /**
     * Tests modifyPalette() without any existing huh.filter types.
     */
    public function testModifyPaletteWithoutFilterTypes()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', ['filter' => [[]]]);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->modifyPalette($this->getDataContainerMock()));
    }

    /**
     * Tests modifyPalette() without found huh.filter type.
     */
    public function testModifyPaletteWithoutFoundFilterType()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'text'];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', ['filter' => ['types' => [['name' => 'choice']]]]);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->modifyPalette($this->getDataContainerMock()));
    }

    /**
     * Tests modifyPalette() on successfully switch type palette with initial palette.
     */
    public function testModifyInitialPalette()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'text', 'isInitial' => true];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'palettes' => [
                'text' => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;',
            ],
        ];

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'text',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TextType',
                        'type' => 'text',
                    ],
                ],
            ],
        ]);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);
        $instance->modifyPalette($this->getDataContainerMock());

        $this->assertSame(FilterConfigElement::INITIAL_PALETTE, $GLOBALS['TL_DCA']['tl_filter_config_element']['palettes']['text']);
    }

    /**
     * Tests prepareChoiceTypes() without existing tl_filter_config_element model.
     */
    public function testPrepareChoiceTypesWithoutFilterConfigElementModel()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->prepareChoiceTypes($this->getDataContainerMock()));
    }

    /**
     * Tests prepareChoiceTypes() without any existing huh.filter types.
     */
    public function testPrepareChoiceTypesWithoutFilterTypes()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', ['filter' => [[]]]);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->prepareChoiceTypes($this->getDataContainerMock()));
    }

    /**
     * Tests prepareChoiceTypes() without found huh.filter type.
     */
    public function testPrepareChoiceTypesWithoutFoundFilterClass()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'text'];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', ['filter' => ['types' => [['name' => 'choice']]]]);

        System::setContainer($container);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->prepareChoiceTypes($this->getDataContainerMock()));
    }

    /**
     * Tests prepareChoiceTypes() without any existing huh.filter types.
     */
    public function testPrepareChoiceWithoutFilter()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test', 'dataContainer' => 'tl_test'];
        $filterConfigModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'choice'];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        // mock system classes
        $controllerAdapter = $this->mockAdapter([
            'loadDataContainer',
        ]);

        $framework = $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
                Model::class => $modelAdapter,
                FilterConfigModel::class => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($framework, $session);
        $filterRegistry = new FilterRegistry($framework, $filterSession);
        $container->set('huh.filter.registry', $filterRegistry);

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));

        $filterConfig = new FilterConfig($framework, $filterSession, $queryBuilder);
        $container->set('huh.filter.config', $filterConfig);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $translator = new Translator('en');
        $container->set('translator', $translator);

        $containerUtil = new ContainerUtil($framework);
        $container->set('huh.utils.container', $containerUtil);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'choice',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ChoiceType',
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernel);

        $dcaAdapter = $this->mockAdapter(['getFields']);
        $dcaAdapter->method('getFields')->willReturn(['success']);
        $container->set('huh.utils.dca', $dcaAdapter);

        System::setContainer($container);

        $filterChoiceFieldOptions = new FieldOptionsChoice($framework);
        $container->set('huh.filter.choice.field_options', $filterChoiceFieldOptions);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->prepareChoiceTypes($this->getDataContainerMock()));
    }

    /**
     * Tests prepareChoiceTypes() with existing huh.filter but type is not instance of ChoiceType.
     */
    public function testPrepareChoiceForNonChoiceType()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test', 'dataContainer' => 'tl_test'];
        $filterConfigModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'text'];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        // mock system classes
        $controllerAdapter = $this->mockAdapter([
            'loadDataContainer',
        ]);

        $framework = $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
                Model::class => $modelAdapter,
                FilterConfigModel::class => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($framework, $session);
        $filterRegistry = new FilterRegistry($framework, $filterSession);
        $container->set('huh.filter.registry', $filterRegistry);

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));

        $filterConfig = new FilterConfig($framework, $filterSession, $queryBuilder);

        $container->set('huh.filter.config', $filterConfig);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $translator = new Translator('en');
        $container->set('translator', $translator);

        $containerUtil = new ContainerUtil($framework);
        $container->set('huh.utils.container', $containerUtil);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'text',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TextType',
                        'type' => 'text',
                    ],
                ],
            ],
        ]);

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernel);

        $dcaAdapter = $this->mockAdapter(['getFields']);
        $dcaAdapter->method('getFields')->willReturn(['success']);
        $container->set('huh.utils.dca', $dcaAdapter);

        System::setContainer($container);

        $filterChoiceFieldOptions = new FieldOptionsChoice($framework);
        $container->set('huh.filter.choice.field_options', $filterChoiceFieldOptions);

        $instance = new FilterConfigElement($framework);

        $this->assertNull($instance->prepareChoiceTypes($this->getDataContainerMock()));
    }

    /**
     * Tests prepareChoiceTypes() for valid choice type.
     */
    public function testPrepareChoiceForValidChoiceType()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test', 'dataContainer' => 'tl_test'];
        $filterConfigModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $GLOBALS['TL_FFL']['select'] = FormSelectMenu::class;

        $GLOBALS['TL_DCA']['tl_test']['fields']['test'] = [
            'label' => 'foo',
            'inputType' => 'select',
            'options' => ['foo', 'bar'],
            'options_callback' => null,
            'eval' => [
                'submitOnChange' => false,
                'allowHtml' => false,
                'rte' => false,
                'preserveTags' => false,
                'isAssociative' => true,
                'includeBlankOption' => false,
                'sql' => '',
            ],
        ];

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'choice', 'field' => 'test'];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        // mock system classes
        $controllerAdapter = $this->mockAdapter([
            'loadDataContainer',
        ]);

        $framework = $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
                Model::class => $modelAdapter,
                FilterConfigModel::class => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($framework, $session);
        $filterRegistry = new FilterRegistry($framework, $filterSession);
        $container->set('huh.filter.registry', $filterRegistry);

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));

        $filterConfig = new FilterConfig($framework, $filterSession, $queryBuilder);
        $container->set('huh.filter.config', $filterConfig);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $translator = new Translator('en');
        $container->set('translator', $translator);

        $containerUtil = new ContainerUtil($framework);
        $container->set('huh.utils.container', $containerUtil);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'choice',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ChoiceType',
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernel);

        $dcaAdapter = $this->mockAdapter(['getFields']);
        $dcaAdapter->method('getFields')->willReturn(['success']);
        $container->set('huh.utils.dca', $dcaAdapter);

        System::setContainer($container);

        $filterChoiceFieldOptions = new FieldOptionsChoice($framework);
        $container->set('huh.filter.choice.field_options', $filterChoiceFieldOptions);

        $dca = &$GLOBALS['TL_DCA']['tl_filter_config_element'];

        $dca['fields'] = [
            'initialValueType' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValueType'],
                'exclude' => true,
                'filter' => true,
                'inputType' => 'select',
                'options' => \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPES,
                'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference'],
                'eval' => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
                'sql' => "varchar(16) NOT NULL default ''",
            ],
            'initialValue' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValue'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
                'sql' => "varchar(128) NOT NULL default ''",
            ],
            'initialValueArray' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValue'],
                'inputType' => 'multiColumnEditor',
                'eval' => [
                    'tl_class' => 'long clr',
                    'multiColumnEditor' => [
                        'fields' => [
                            'value' => [
                                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['initialValue_value'],
                                'exclude' => true,
                                'search' => true,
                                'inputType' => 'text',
                                'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'groupStyle' => 'width: 200px'],
                            ],
                        ],
                    ],
                ],
                'sql' => 'blob NULL',
            ],
            'addDefaultValue' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['addDefaultValue'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'w50', 'submitOnChange' => true],
                'sql' => "char(1) NOT NULL default ''",
            ],
            'defaultValueType' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValueType'],
                'exclude' => true,
                'filter' => true,
                'inputType' => 'select',
                'options' => \HeimrichHannot\FilterBundle\Filter\AbstractType::VALUE_TYPES,
                'reference' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['reference'],
                'eval' => ['tl_class' => 'w50 clr', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
                'sql' => "varchar(16) NOT NULL default ''",
            ],
            'defaultValue' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValue'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
                'sql' => "varchar(128) NOT NULL default ''",
            ],
            'defaultValueArray' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValue'],
                'inputType' => 'multiColumnEditor',
                'eval' => [
                    'tl_class' => 'long clr',
                    'multiColumnEditor' => [
                        'fields' => [
                            'value' => [
                                'label' => &$GLOBALS['TL_LANG']['tl_filter_config_element']['defaultValue_value'],
                                'exclude' => true,
                                'search' => true,
                                'inputType' => 'text',
                                'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'groupStyle' => 'width: 200px'],
                            ],
                        ],
                    ],
                ],
                'sql' => 'blob NULL',
            ],
        ];

        $instance = new FilterConfigElement($framework);
        $instance->prepareChoiceTypes($this->getDataContainerMock());

        $this->assertSame('select', $dca['fields']['defaultValue']['inputType']);
        $this->assertNotEmpty($dca['fields']['defaultValue']['options']);
        $this->assertSame(['foo', 'bar'], $dca['fields']['defaultValue']['options']);
        $this->assertTrue($dca['fields']['defaultValue']['eval']['chosen']);

        $this->assertSame('select', $dca['fields']['initialValue']['inputType']);
        $this->assertNotEmpty($dca['fields']['initialValue']['options']);
        $this->assertSame(['foo', 'bar'], $dca['fields']['initialValue']['options']);
        $this->assertTrue($dca['fields']['initialValue']['eval']['chosen']);

        $this->assertSame('select', $dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['inputType']);
        $this->assertNotEmpty($dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['options']);
        $this->assertSame(['foo', 'bar'], $dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['options']);
        $this->assertTrue($dca['fields']['defaultValueArray']['eval']['multiColumnEditor']['fields']['value']['eval']['chosen']);

        $this->assertSame('select', $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['inputType']);
        $this->assertNotEmpty($dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['options']);
        $this->assertSame(['foo', 'bar'], $dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['options']);
        $this->assertTrue($dca['fields']['initialValueArray']['eval']['multiColumnEditor']['fields']['value']['eval']['chosen']);
    }

    /**
     * @return DataContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDataContainerMock(array $properties = [])
    {
        if (empty($properties)) {
            $properties = ['id' => 1, 'table' => 'tl_filter_config_element'];
        }

        return $this->mockClassWithProperties(DataContainer::class, $properties);
    }
}
