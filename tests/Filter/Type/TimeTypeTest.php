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
use HeimrichHannot\FilterBundle\Filter\Type\TimeType;
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

class TimeTypeTest extends ContaoTestCase
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

        $type = new TimeType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\TimeType', $type);
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

        $type = new TimeType($config);

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

        $type = new TimeType($config);

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
                        'name' => 'time',
                        'class' => TimeType::class,
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
                        'name' => 'time',
                        'class' => TimeType::class,
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
        $element->type = 'time';
        $element->name = 'start';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\TimeType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_CHOICE, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
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
                        'name' => 'time',
                        'class' => TimeType::class,
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
        $element->type = 'time';
        $element->name = 'start';
        $element->minTime = '11:34';
        $element->maxTime = '23:34';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\TimeType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_CHOICE, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
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
                        'name' => 'time',
                        'class' => TimeType::class,
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
        $element->type = 'time';
        $element->name = 'start';
        $element->minTime = '{{date::H:i}}';
        $element->maxTime = '23:49';
        $element->timeWidget = DateType::WIDGET_TYPE_SINGLE_TEXT;

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\TimeType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_SINGLE_TEXT, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
        $this->assertSame('timepicker', $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('group_attr')['class']);
        $this->assertTrue((bool) $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-enable-time']);
        $this->assertSame('H:i', $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-date-format']);
        $this->assertSame(Date::parse('H:i', time()), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-min-date']);
        $this->assertSame('23:49', $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['data-max-date']);
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
                        'name' => 'time',
                        'class' => TimeType::class,
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
        $element->type = 'time';
        $element->name = 'start';
        $element->minTime = '{{date::H:i}}';
        $element->maxTime = '23:59';
        $element->timeWidget = DateType::WIDGET_TYPE_SINGLE_TEXT;
        $element->html5 = true;

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count()); // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('start'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\TimeType::class, $config->getBuilder()->get('start')->getType()->getInnerType());
        $this->assertSame(DateType::WIDGET_TYPE_SINGLE_TEXT, $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('widget'));
        $this->assertTrue((bool) $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('html5'));
        $this->assertSame(Date::parse('\TH:i', time()), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['min']);
        $this->assertSame(Date::parse('\TH:i', System::getContainer()->get('huh.utils.date')->getTimeStamp('23:59')), $config->getBuilder()->get('start')->getForm()->getConfig()->getOption('attr')['max']);
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
                        'name' => 'time',
                        'class' => TimeType::class,
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
        $start->type = 'time';
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
                        'name' => 'time',
                        'class' => TimeType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'time', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'time';
        $start->name = 'start';
        $start->field = 'start';
        $start->minTime = '11:45';
        $start->maxTime = '23:59';

        $config->init('test', $filter, [$start]);
        $config->initQueryBuilder();

        $minDate = System::getContainer()->get('huh.utils.date')->getTimeStamp('11:45');

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
                        'name' => 'time',
                        'class' => TimeType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'time', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'time';
        $start->name = 'start';
        $start->field = 'start';
        $start->minTime = '11:30';
        $start->maxTime = '23:59';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '09:20']);
        $config->initQueryBuilder();

        $minDate = System::getContainer()->get('huh.utils.date')->getTimeStamp('11:30');

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
                        'name' => 'time',
                        'class' => TimeType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'time', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'time';
        $start->name = 'start';
        $start->field = 'start';
        $start->minTime = '{{date::H:i}}';
        $start->maxTime = '12.12.2100 11:48';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '12.12.2100 11:50']);
        $config->initQueryBuilder();

        $maxDate = System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2100 11:48');

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
                        'name' => 'time',
                        'class' => TimeType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'time', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'time';
        $start->name = 'start';
        $start->field = 'start';
        $start->minTime = '{{date::H:i}}';
        $start->maxTime = '12.12.2100 11:48';
        $start->isInitial = true;
        $start->initialValue = '12.12.2095 11:11';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '12.12.2095 11:50']);
        $config->initQueryBuilder();

        $value = System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2095 11:50');

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
                        'name' => 'time',
                        'class' => TimeType::class,
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['start'] = [
            'inputType' => 'text',
            'label' => ['start', ''],
            'eval' => ['rgxp' => 'time', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $start = new FilterConfigElementModel();
        $start->id = 2;
        $start->type = 'time';
        $start->name = 'start';
        $start->field = 'start';
        $start->minTime = '{{date::H:i}}';
        $start->maxTime = '12.12.2100 23:11';

        $config->init('test', $filter, [$start]);
        $config->setData(['start' => '12.12.2099 11:11']);
        $config->initQueryBuilder();

        $value = System::getContainer()->get('huh.utils.date')->getTimeStamp('12.12.2099 11:11');

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
        return __DIR__.DIRECTORY_SEPARATOR.'../..'.DIRECTORY_SEPARATOR.'Fixtures';
    }
}
