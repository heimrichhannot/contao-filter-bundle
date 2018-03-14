<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\Filter\Type\DateRangeType;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class DateRangeTypeTest extends ContaoTestCase
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

        $GLOBALS['TL_LANGUAGE']    = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_test'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql'           => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [

            ]
        ];

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql'           => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [

            ]
        ];

        $finder = new ResourceFinder([
            $this->getFixturesDir() . '/vendor/contao/core-bundle/Resources/contao',
        ]);

        $this->container = $this->mockContainer();
        $this->container->set('contao.resource_finder', $finder);
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->set('translator', new Translator('en'));

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
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $type = new DateRangeType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\DateRangeType', $type);
    }

    /**
     * Test getDefaultOperator()
     */
    public function testGetDefaultOperator()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        /** @var FilterConfigElementModel $element */
        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, []);

        $type = new DateRangeType($config);

        $this->assertEquals(DatabaseUtil::OPERATOR_LIKE, $type->getDefaultOperator($element));
    }

    /**
     * Test getDefaultName()
     */
    public function testGetDefaultName()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $range       = new FilterConfigElementModel();
        $range->name = 'test';

        $type = new DateRangeType($config);

        $this->assertEquals('test', $type->getDefaultName($range));
    }

    /**
     * Test buildForm() without wrapper name
     */
    public function testBuildFormWithoutWrapperName()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range       = new FilterConfigElementModel();
        $range->type = 'date_range';

        $config->init('test', $filter, [$range]);
        $config->buildForm();

        $this->assertEquals(1, $config->getBuilder()->count()); // f_id element always exists
    }

    /**
     * Test buildForm() without wrapper element
     */
    public function testBuildFormWithoutWrapper()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $config->init('test', $filter, []);
        $config->buildForm();

        $range       = new FilterConfigElementModel();
        $range->type = 'date_range';

        $type = new DateRangeType($config);
        $type->buildForm($range, $config->getBuilder());

        $this->assertEquals(1, $config->getBuilder()->count()); // f_id element always exists
    }

    /**
     * Test buildForm() without wrapper start stop element
     */
    public function testBuildFormWithoutStartStopElement()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range       = new FilterConfigElementModel();
        $range->name = 'range';
        $range->type = 'date_range';

        $config->init('test', $filter, [$range]);
        $config->buildForm();

        $this->assertEquals(1, $config->getBuilder()->count()); // f_id element always exists
        $this->assertFalse($config->getBuilder()->has('range'));
    }

    /**
     * Test buildForm() without wrapper start stop element types
     */
    public function testBuildFormWithoutStopElementType()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start       = new FilterConfigElementModel();
        $start->id   = 2;
        $start->type = 'date';
        $start->name = 'start';

        $stop       = new FilterConfigElementModel();
        $stop->id   = 3;
        $stop->type = 'date_time';
        $stop->name = 'stop';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->buildForm();

        $this->assertEquals(1, $config->getBuilder()->count()); // f_id element always exists
        $this->assertFalse($config->getBuilder()->has('range'));
    }

    /**
     * Test buildForm() without wrapper element
     */
    public function testBuildFormWithStartStopElement()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start       = new FilterConfigElementModel();
        $start->id   = 2;
        $start->type = 'date';
        $start->name = 'start';

        $stop       = new FilterConfigElementModel();
        $stop->id   = 3;
        $stop->type = 'date_time';
        $stop->name = 'stop';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->buildForm();

        $this->assertEquals(2, $config->getBuilder()->count()); // f_id element always exists
        $this->assertTrue($config->getBuilder()->has('range'));
        $this->assertTrue($config->getBuilder()->get('range')->has('start'));
        $this->assertTrue($config->getBuilder()->get('range')->has('stop'));
        $this->assertInstanceOf(DateType::class, $config->getBuilder()->get('range')->get('start')->getType()->getInnerType());
        $this->assertInstanceOf(DateTimeType::class, $config->getBuilder()->get('range')->get('stop')->getType()->getInnerType());
    }

    /**
     * Test buildForm() without start or stop element
     */
    public function testBuildQueryWithoutElements()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start       = new FilterConfigElementModel();
        $start->id   = 2;
        $start->type = 'date';
        $start->name = 'start';

        $config->init('test', $filter, [$range, $start]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertEmpty($config->getQueryBuilder()->getQueryPart('where'));
    }

    /**
     * Test buildQuery() without start/stop fields
     */
    public function testBuildQueryWithoutFields()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start       = new FilterConfigElementModel();
        $start->id   = 2;
        $start->type = 'date';
        $start->name = 'start';

        $stop       = new FilterConfigElementModel();
        $stop->id   = 3;
        $stop->type = 'date_time';
        $stop->name = 'stop';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertEmpty($config->getQueryBuilder()->getQueryPart('where'));
    }

    /**
     * Test buildQuery() statement with different start stop fields and start is date
     */
    public function testBuildQueryWithDifferentStartStopFieldsAndStartDate()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start        = new FilterConfigElementModel();
        $start->id    = 2;
        $start->type  = 'date';
        $start->name  = 'start';
        $start->field = 'start';

        $stop        = new FilterConfigElementModel();
        $stop->id    = 3;
        $stop->type  = 'date_time';
        $stop->name  = 'stop';
        $stop->field = 'stop';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE ((:start >= tl_test.start) AND (:start <= tl_test.stop)) OR ((:stop >= tl_test.start) AND (:stop <= tl_test.stop)) OR ((:start <= tl_test.start) AND (:stop >= tl_test.stop))', $config->getQueryBuilder()->getSQL());

        $this->assertEquals(0, $config->getQueryBuilder()->getParameter(':start'));
        $this->assertEquals(9999999999999, $config->getQueryBuilder()->getParameter(':stop'));
    }


    /**
     * Test buildQuery() statement with different start stop fields and start is time
     */
    public function testBuildQueryWithDifferentStartStopFieldsAndMinMaxStartTime()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TimeType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start          = new FilterConfigElementModel();
        $start->id      = 2;
        $start->type    = 'time';
        $start->name    = 'start';
        $start->field   = 'start';
        $start->minTime = '1511022657';
        $start->maxTime = '1591022657';

        $stop        = new FilterConfigElementModel();
        $stop->id    = 3;
        $stop->type  = 'date_time';
        $stop->name  = 'stop';
        $stop->field = 'stop';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE ((:start >= tl_test.start) AND (:start <= tl_test.stop)) OR ((:stop >= tl_test.start) AND (:stop <= tl_test.stop)) OR ((:start <= tl_test.start) AND (:stop >= tl_test.stop))', $config->getQueryBuilder()->getSQL());

        $this->assertEquals(1511022657, $config->getQueryBuilder()->getParameter(':start'));
        $this->assertEquals(9999999999999, $config->getQueryBuilder()->getParameter(':stop'));
    }


    /**
     * Test buildQuery() statement with different start stop fields and start is time
     */
    public function testBuildQueryWithDifferentStartStopFieldsAndMinMaxStartDateTimeAndStopDate()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start              = new FilterConfigElementModel();
        $start->id          = 2;
        $start->type        = 'date_time';
        $start->name        = 'start';
        $start->field       = 'start';
        $start->minDateTime = '1511022657';
        $start->maxDateTime = '1591022657';

        $stop          = new FilterConfigElementModel();
        $stop->id      = 3;
        $stop->type    = 'date';
        $stop->name    = 'stop';
        $stop->field   = 'stop';
        $stop->minDate = '1311022657';
        $stop->maxDate = '1891022657';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE ((:start >= tl_test.start) AND (:start <= tl_test.stop)) OR ((:stop >= tl_test.start) AND (:stop <= tl_test.stop)) OR ((:start <= tl_test.start) AND (:stop >= tl_test.stop))', $config->getQueryBuilder()->getSQL());

        $this->assertEquals(1511022657, $config->getQueryBuilder()->getParameter(':start'));
        $this->assertEquals(1891022657, $config->getQueryBuilder()->getParameter(':stop'));
    }

    /**
     * Test buildQuery() statement with different start stop fields and start is time
     */
    public function testBuildQueryWithDifferentStartStopFieldsAndMinMaxStartDateTimeAndStopDateWithInitialData()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start              = new FilterConfigElementModel();
        $start->id          = 2;
        $start->type        = 'date_time';
        $start->name        = 'start';
        $start->field       = 'start';
        $start->minDateTime = '1511022657';
        $start->maxDateTime = '1591022657';

        $stop          = new FilterConfigElementModel();
        $stop->id      = 3;
        $stop->type    = 'date';
        $stop->name    = 'stop';
        $stop->field   = 'stop';
        $stop->minDate = '1311022657';
        $stop->maxDate = '1891022657';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->setData(['range' => ['start' => 1520261184, 'stop' => 1521038784]]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE ((:start >= tl_test.start) AND (:start <= tl_test.stop)) OR ((:stop >= tl_test.start) AND (:stop <= tl_test.stop)) OR ((:start <= tl_test.start) AND (:stop >= tl_test.stop))', $config->getQueryBuilder()->getSQL());

        $this->assertEquals(1520261184, $config->getQueryBuilder()->getParameter(':start'));
        $this->assertEquals(1521038784, $config->getQueryBuilder()->getParameter(':stop'));
    }

    /**
     * Test buildQuery() statement with different start stop fields and start is time
     */
    public function testBuildQueryWithSameStartStopFieldsAndMinMaxStartDateTimeAndStopDate()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'    => 'date_range',
                        'class'   => 'HeimrichHannot\FilterBundle\Filter\Type\DateRangeType',
                        'type'    => 'date',
                        'wrapper' => true
                    ],
                    [
                        'name'  => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type'  => 'date',
                    ],
                    [
                        'name'  => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type'  => 'date',
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.utils.date', new DateUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $range               = new FilterConfigElementModel();
        $range->id           = 1;
        $range->name         = 'range';
        $range->type         = 'date_range';
        $range->startElement = 2;
        $range->stopElement  = 3;

        $start              = new FilterConfigElementModel();
        $start->id          = 2;
        $start->type        = 'date_time';
        $start->name        = 'start';
        $start->field       = 'start';
        $start->minDateTime = '1511022657';
        $start->maxDateTime = '1591022657';

        $stop          = new FilterConfigElementModel();
        $stop->id      = 3;
        $stop->type    = 'date';
        $stop->name    = 'start';
        $stop->field   = 'start';
        $stop->minDate = '1311022657';
        $stop->maxDate = '1891022657';

        $config->init('test', $filter, [$range, $start, $stop]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE (:start <= tl_test.start) AND (:stop >= tl_test.start)', $config->getQueryBuilder()->getSQL());

        $this->assertEquals(1511022657, $config->getQueryBuilder()->getParameter(':start'));
        $this->assertEquals(1891022657, $config->getQueryBuilder()->getParameter(':stop'));
    }


    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../..' . DIRECTORY_SEPARATOR . 'Fixtures';
    }
}
