<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\Date;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\DateType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class DateTypeTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    protected function setUp()
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_test'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql' => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [
            ],
        ];

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql' => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [
            ],
        ];

        $finder = new ResourceFinder([
            $this->getFixturesDir().'/vendor/contao/core-bundle/Resources/contao',
        ]);

        $this->container = $this->mockContainer();
        $this->container->set('contao.resource_finder', $finder);
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->set('translator', new Translator('en'));

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('filter_frontend_submit', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            return '/_filter/submit/1';
        }));

        $this->container->set('router', $router);

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        $this->container->set('kernel', $this->kernel);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $type = new DateType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\DateType', $type);
    }

    /**
     * Test getDefaultOperator().
     */
    public function testGetDefaultOperator()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        /** @var FilterConfigElementModel $element */
        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, []);

        $type = new DateType($config);

        $this->assertSame(DatabaseUtil::OPERATOR_EQUAL, $type->getDefaultOperator($element));
    }

    /**
     * Test getDefaultName().
     */
    public function testGetDefaultName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $range = new FilterConfigElementModel();
        $range->name = 'test';

        $type = new DateType($config);

        $this->assertSame('test', $type->getDefaultName($range));
    }

    /**
     * Test buildForm() without name.
     */
    public function testBuildFormWithoutName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'date';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(2, $config->getBuilder()->count());  // f_id and f_ref element always exists
    }

    /**
     * Test buildForm() with name as choice widget.
     */
    public function testBuildFormChoiceWithName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'date';
        $element->name = 'start';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $year = date('Y', time());

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\DateType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_CHOICE, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
        $this->assertSame(range($year - 5, $year + 5), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('years'));
    }

    /**
     * Test buildForm() with min and max date as choice widget.
     */
    public function testBuildFormChoiceWithMinAndMaxDate()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'date';
        $element->name = 'start';
        $element->minDate = '{{date::d.m.Y}}';
        $element->maxDate = '12.12.2100 12:34';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\DateType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_CHOICE, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
        $this->assertSame(range(Date::parse('Y', time()), 2100), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('years'));
    }

    /**
     * Test buildForm() with min and max date as single_text widget.
     */
    public function testBuildFormSingleTextWithMinAndMaxDate()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'date';
        $element->name = 'start';
        $element->minDate = '{{date::d.m.Y}}';
        $element->maxDate = '12.12.2100 12:34';
        $element->dateWidget = DateType::WIDGET_TYPE_SINGLE_TEXT;

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\DateType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_SINGLE_TEXT, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
        $this->assertSame('datepicker', $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('group_attr')['class']);
        $this->assertFalse((bool) $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-enable-time']);
        $this->assertSame('d.m.Y', $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-date-format']);
        $this->assertSame(Date::parse('d.m.Y', time()), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-min-date']);
        $this->assertSame('12.12.2100', $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-max-date']);
    }

    /**
     * Test buildForm() with min and max date as single_text html5 widget.
     */
    public function testBuildFormSingleTextHtml5WithMinAndMaxDate()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'date';
        $element->name = 'start';
        $element->minDate = '{{date::d.m.Y}}';
        $element->maxDate = '12.12.2100 12:34';
        $element->dateWidget = DateType::WIDGET_TYPE_SINGLE_TEXT;
        $element->html5 = true;

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\DateType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_SINGLE_TEXT, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
        $this->assertTrue((bool) $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('html5'));
        $this->assertSame(Date::parse('Y-m-d', time()), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['min']);
        $this->assertSame(Date::parse('Y-m-d', System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2100')), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['max']);
    }

    /**
     * Test buildQuery() without field.
     */
    public function testBuildQueryWithoutField()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'date';
        $start->name = 'start';

        $config->init('test', $filter, [$start]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertEmpty($config->getQueryBuilder()->getQueryPart('where'));
    }

    /**
     * Test buildQuery() without value data.
     */
    public function testBuildQueryWithoutData()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'date';
        $start->name = 'start';
        $start->field = 'start';
        $start->minDate = '{{date::d.m.Y}}';
        $start->maxDate = '12.12.2100';

        $config->init('test', $filter, [$start]);
        $config->initQueryBuilder();

        $minDate = System::getContainer()->get('huh.utils.date')->getTimeStamp('{{date::d.m.Y}}');

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertSame('SELECT  FROM tl_test WHERE (:start <= tl_test.start) AND (:stop >= tl_test.start)', $config->getQueryBuilder()->getSQL());
        $this->assertSame([':start' => $minDate, ':stop' => $minDate], $config->getQueryBuilder()->getParameters());
    }

    /**
     * Test buildQuery() with data beyond min date time.
     */
    public function testBuildQueryWithDataBeyondMinDateTime()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'date';
        $start->name = 'start';
        $start->field = 'start';
        $start->minDate = '{{date::d.m.Y}}';
        $start->maxDate = '12.12.2100';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '01.01.1981']);
        $config->initQueryBuilder();

        $minDate = System::getContainer()->get('huh.utils.date')->getTimeStamp('{{date::d.m.Y}}');

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertSame('SELECT  FROM tl_test WHERE (:start <= tl_test.start) AND (:stop >= tl_test.start)', $config->getQueryBuilder()->getSQL());
        $this->assertSame([':start' => $minDate, ':stop' => $minDate], $config->getQueryBuilder()->getParameters());
    }

    /**
     * Test buildQuery() with data beyond max date time.
     */
    public function testBuildQueryWithDataBeyondMaxDateTime()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'date';
        $start->name = 'start';
        $start->field = 'start';
        $start->minDate = '{{date::d.m.Y}}';
        $start->maxDate = '12.12.2100';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '12.12.2101']);
        $config->initQueryBuilder();

        $maxDate = System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2100');

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertSame('SELECT  FROM tl_test WHERE (:start <= tl_test.start) AND (:stop >= tl_test.start)', $config->getQueryBuilder()->getSQL());
        $this->assertSame([':start' => $maxDate, ':stop' => $maxDate], $config->getQueryBuilder()->getParameters());
    }

    /**
     * Test buildQuery() with initial data.
     */
    public function testBuildQueryInitial()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'date';
        $start->name = 'start';
        $start->field = 'start';
        $start->minDate = '{{date::d.m.Y}}';
        $start->maxDate = '12.12.2100';
        $start->isInitial = true;
        $start->initialValue = '12.12.2095';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '12.12.2095']);
        $config->initQueryBuilder();

        $value = System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2095');

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertSame('SELECT  FROM tl_test WHERE (:start <= tl_test.start) AND (:stop >= tl_test.start)', $config->getQueryBuilder()->getSQL());
        $this->assertSame([':start' => $value, ':stop' => $value], $config->getQueryBuilder()->getParameters());
    }

    /**
     * Test buildQuery().
     */
    public function testBuildQuery()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => DateType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'date';
        $start->name = 'start';
        $start->field = 'start';
        $start->minDate = '{{date::d.m.Y}}';
        $start->maxDate = '12.12.2100';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '12.12.2099']);
        $config->initQueryBuilder();

        $value = System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2099');

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertSame('SELECT  FROM tl_test WHERE (:start <= tl_test.start) AND (:stop >= tl_test.start)', $config->getQueryBuilder()->getSQL());
        $this->assertSame([':start' => $value, ':stop' => $value], $config->getQueryBuilder()->getParameters());
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'../..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
