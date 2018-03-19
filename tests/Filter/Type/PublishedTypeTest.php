<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\PublishedType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class PublishedTypeTest extends ContaoTestCase
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

        $type = new PublishedType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\PublishedType', $type);
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

        $type = new PublishedType($config);

        $this->assertEquals(DatabaseUtil::OPERATOR_LIKE, $type->getDefaultOperator($element));
    }

    /**
     * Test buildForm() with field name
     */
    public function testBuildFormWithFieldName()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'visible',
                        'class' => PublishedType::class,
                        'type'  => 'text'
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element        = new FilterConfigElementModel();
        $element->type  = 'visible';
        $element->field = 'test';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertEquals(1, $config->getBuilder()->count()); // f_id element always exists
        $this->assertFalse($config->getBuilder()->has('test'));
    }

    /**
     * Test buildQuery() without dca field
     */
    public function testBuildQueryWithoutDcaField()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'visible',
                        'class' => PublishedType::class,
                        'type'  => 'text'
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element       = new FilterConfigElementModel();
        $element->id   = 2;
        $element->type = 'visible';
        $element->name = 'test';

        $config->init('test', $filter, [$element]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertEmpty($config->getQueryBuilder()->getQueryPart('where'));
    }

    /**
     * Test buildQuery() without start stop
     */
    public function testBuildQueryWithoutStartStop()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'visible',
                        'class' => PublishedType::class,
                        'type'  => 'other'
                    ]
                ]
            ]
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['published'] = [
            'inputType' => 'checkbox',
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $element        = new FilterConfigElementModel();
        $element->id    = 2;
        $element->type  = 'visible';
        $element->field = 'published';

        $config->init('test', $filter, [$element]);
        $config->setData(['published' => 1]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE tl_test.published = 1', $config->getQueryBuilder()->getSQL());
    }

    /**
     * Test buildQuery() with start stop
     */
    public function testBuildQueryWithStartStop()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'visible',
                        'class' => PublishedType::class,
                        'type'  => 'other'
                    ]
                ]
            ]
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['published'] = [
            'inputType' => 'checkbox',
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $element                  = new FilterConfigElementModel();
        $element->id              = 2;
        $element->type            = 'visible';
        $element->field           = 'published';
        $element->addStartAndStop = true;
        $element->startField      = 'start';
        $element->stopField       = 'stop';

        $config->init('test', $filter, [$element]);
        $config->setData(['published' => 1]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE ((tl_test.start = "") OR (tl_test.start <= :startField_time)) AND ((tl_test.stop = "") OR (tl_test.stop > :stopField_time)) AND (tl_test.published = 1)', $config->getQueryBuilder()->getSQL());
    }

    /**
     * Test buildQuery() with start stop and active frontend preview
     */
    public function testBuildQueryWithStartStopAndPreview()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'visible',
                        'class' => PublishedType::class,
                        'type'  => 'other'
                    ]
                ]
            ]
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['published'] = [
            'inputType' => 'checkbox',
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $element                  = new FilterConfigElementModel();
        $element->id              = 2;
        $element->type            = 'visible';
        $element->field           = 'published';
        $element->addStartAndStop = true;
        $element->startField      = 'start';
        $element->stopField       = 'stop';
        $element->ignoreFePreview = true;

        $config->init('test', $filter, [$element]);
        $config->setData(['published' => 1]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertEquals('SELECT  FROM tl_test WHERE ((tl_test.start = "") OR (tl_test.start <= :startField_time)) AND ((tl_test.stop = "") OR (tl_test.stop > :stopField_time)) AND (tl_test.published = 1)', $config->getQueryBuilder()->getSQL());
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../..' . DIRECTORY_SEPARATOR . 'Fixtures';
    }
}
