<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Model;

use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class FilterConfigElementModelTest extends ContaoTestCase
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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

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

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->setParameter('kernel.cache_dir', $this->getFixturesDir());

        $connection = $this->createMock(Connection::class);
        $connection
            ->method('getDatabasePlatform')
            ->willReturn(new MySqlPlatform());

        $connection
            ->expects(!empty($metadata) ? $this->once() : $this->never())
            ->method('getSchemaManager')
            ->willReturn(new MySqlSchemaManager($connection));

        $this->container->set('database_connection', $connection);

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);
    }

    /**
     * Test findPublishedByPid().
     */
    public function testFindPublishedByPid()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
            'pid' => 1,
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([
            $modelA,
        ]);

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $modelAdapter]);

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigElementModel = new FilterConfigElementModel();
        $result = $filterConfigElementModel->findPublishedByPid(1);

        $this->assertNotNull($result);
        $this->assertSame($modelA, $result[0]);
    }

    /**
     * Test findPublishedByPid().
     */
    public function testFindPublishedByPidWithLimit()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
            'pid' => 1,
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([
            $modelA,
        ]);

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $modelAdapter]);

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigElementModel = new FilterConfigElementModel();
        $result = $filterConfigElementModel->findPublishedByPid(1, 1);

        $this->assertNotNull($result);
        $this->assertSame($modelA, $result[0]);
    }

    /**
     * Test findPublishedByPid() without Contao\Model adapter.
     */
    public function testFindPublishedByPidWithoutModelAdapter()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
            'pid' => 1,
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([
            $modelA,
        ]);

        $framework = $this->mockContaoFramework();

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigElementModel = new FilterConfigElementModel();
        $result = $filterConfigElementModel->findPublishedByPid(1);

        $this->assertNull($result);
    }

    /**
     * Test findPublishedByPidAndTypes() without Contao\Model adapter.
     */
    public function testFindPublishedByPidAndTypesWithoutModelAdapter()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
            'pid' => 1,
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([
            $modelA,
        ]);

        $framework = $this->mockContaoFramework();

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigElementModel = new FilterConfigElementModel();
        $result = $filterConfigElementModel->findPublishedByPidAndTypes(1);

        $this->assertNull($result);
    }

    /**
     * Test findPublishedByPidAndTypes() with limit.
     */
    public function testFindPublishedByPidAndTypesWithLimit()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
            'pid' => 1,
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([
            $modelA,
        ]);

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $modelAdapter]);

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigElementModel = new FilterConfigElementModel();
        $result = $filterConfigElementModel->findPublishedByPidAndTypes(1, [], 1);

        $this->assertNotNull($result);
        $this->assertSame($modelA, $result[0]);
    }

    /**
     * Test findPublishedByPidAndTypes() with types.
     */
    public function testFindPublishedByPidAndTypesWithTypes()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigElementModel::class, [
            'id' => 1,
            'pid' => 1,
            'type' => 'date',
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([
            $modelA,
        ]);

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $modelAdapter]);

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigElementModel = new FilterConfigElementModel();
        $result = $filterConfigElementModel->findPublishedByPidAndTypes(1, ['date'], 1);

        $this->assertNotNull($result);
        $this->assertSame($modelA, $result[0]);
    }

    /**
     * Test getFormName() without huh.filter.choice.type service.
     */
    public function testGetFormNameWithoutTypeChoiceService()
    {
        $model = new FilterConfigElementModel();
        $model->id = 1;
        $model->pid = 1;
        $model->type = 'date';

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->assertNull($model->getFormName($config));
    }

    /**
     * Test getFormName() without types in huh.filter.choice.type service.
     */
    public function testGetFormNameWithoutTypeChoices()
    {
        $model = new FilterConfigElementModel();
        $model->id = 1;
        $model->pid = 1;
        $model->type = 'date';

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $container = $this->mockContainer();
        $container->setParameter('kernel.debug', true);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        $container->set('kernel', $kernel);

        $container->setParameter('huh.filter', []);

        System::setContainer($container);
        $container->set('huh.filter.choice.type', new TypeChoice($framework));

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->assertNull($model->getFormName($config));
    }

    /**
     * Test getFormName() without current type.
     */
    public function testGetFormNameWithoutType()
    {
        $model = new FilterConfigElementModel();
        $model->id = 1;
        $model->pid = 1;
        $model->type = 'date';

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $container = $this->mockContainer();
        $container->setParameter('kernel.debug', true);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        $container->set('kernel', $kernel);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date_time',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateTimeType',
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        System::setContainer($container);
        $container->set('huh.filter.choice.type', new TypeChoice($framework));

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->assertNull($model->getFormName($config));
    }

    /**
     * Test getFormName() without type name.
     */
    public function testGetFormNameWithoutNoName()
    {
        $model = new FilterConfigElementModel();
        $model->id = 1;
        $model->pid = 1;
        $model->type = 'date';

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $container = $this->mockContainer();
        $container->setParameter('kernel.debug', true);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        $container->set('kernel', $kernel);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $container->setParameter('kernel.default_locale', 'de');
        $container->set('translator', new Translator('en'));

        System::setContainer($container);
        $container->set('huh.filter.choice.type', new TypeChoice($framework));

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->assertNull($model->getFormName($config));
    }

    /**
     * Test getFormName().
     */
    public function testGetFormName()
    {
        $model = new FilterConfigElementModel();
        $model->id = 1;
        $model->pid = 1;
        $model->name = 'start';
        $model->type = 'date';

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $container = $this->mockContainer();
        $container->setParameter('kernel.debug', true);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        $container->set('kernel', $kernel);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'date',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\DateType',
                        'type' => 'date',
                    ],
                ],
            ],
        ]);

        $container->setParameter('kernel.default_locale', 'de');
        $container->set('translator', new Translator('en'));

        System::setContainer($container);
        $container->set('huh.filter.choice.type', new TypeChoice($framework));

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->assertSame('start', $model->getFormName($config));

        // cache test
        $this->assertSame('start', $model->getFormName($config));
    }

    /**
     * Test jsonSerialize().
     */
    public function testJsonSerialize()
    {
        $filterConfigElementModel = new FilterConfigElementModel();
        $filterConfigElementModel->id = 1;
        $filterConfigElementModel->pid = 1;
        $filterConfigElementModel->type = 'date';
        $filterConfigElementModel->fields = ['f1', 'f2'];

        $jsonArray = $filterConfigElementModel->jsonSerialize();

        $this->assertNotEmpty($jsonArray);
        $this->assertArrayHasKey('arrData', $jsonArray);
        $this->assertSame(['id' => 1, 'pid' => 1, 'type' => 'date', 'fields' => ['f1', 'f2']], $jsonArray['arrData']);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
