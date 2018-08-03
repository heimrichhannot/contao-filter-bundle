<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Utils;

use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\Util\FilterConfigElementUtil;
use HeimrichHannot\UtilsBundle\Choice\FieldChoice;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;

class FilterConfigElementHelperTest extends ContaoTestCase
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
     * Tests getFields() with no existing tl_filter_config_element model.
     */
    public function testGetFieldsWithoutConfigElementModel()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn('');

        $framework = $this->mockContaoFramework([Model::class => $modelAdapter]);

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework, $this->createMock(ContainerUtil::class));
        $container->set('huh.utils.model', $modelsUtil);
        System::setContainer($container);

        $filterConfigElementUtil = new FilterConfigElementUtil($framework);

        $this->assertEmpty($filterConfigElementUtil->getFields($this->getDataContainerMock()));
    }

    /**
     * Tests getFields() if no models exists.
     */
    public function testGetFieldsWithoutFilterConfig()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 2];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test', 'dataContainer' => 'tl_test'];
        $filterConfigModel = $this->mockClassWithProperties(FilterConfigModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigModel::class => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework, $this->createMock(ContainerUtil::class));
        $container->set('huh.utils.model', $modelsUtil);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $containerUtil = new ContainerUtil($framework, $this->createMock(FileLocator::class));
        $container->set('huh.utils.container', $containerUtil);

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($framework, $session);
        $filterManager = new FilterManager($framework, $filterSession);
        $container->set('huh.filter.manager', $filterManager);

        $filterConfig = new FilterConfig($container, $framework, new FilterSession($framework, $session), new Connection([], new Driver()));
        $container->set('huh.filter.config', $filterConfig);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getContainer')->willReturn($container);

        $container->set('kernel', $kernel);

        System::setContainer($container);

        $container->set('huh.utils.dca', new DcaUtil($framework));
        $container->set('huh.utils.choice.field', new FieldChoice($framework));

        $filterConfigElementUtil = new FilterConfigElementUtil($framework);

        $this->assertEmpty($filterConfigElementUtil->getFields($this->getDataContainerMock()));
    }

    /**
     * Tests getFields() if no models exists.
     */
    public function testGetFieldsWithExistingChoices()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1];
        $filterConfigElementModel = $this->mockClassWithProperties(FilterConfigModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test', 'dataContainer' => 'tl_test'];
        $filterConfigModel = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $framework = $this->mockContaoFramework(
            [
                Model::class => $modelAdapter,
                FilterConfigModel::class => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework, $this->createMock(ContainerUtil::class));
        $container->set('huh.utils.model', $modelsUtil);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $containerUtil = new ContainerUtil($framework, $this->createMock(FileLocator::class));
        $container->set('huh.utils.container', $containerUtil);

        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($framework, $session);
        $filterManager = new FilterManager($framework, $filterSession);
        $container->set('huh.filter.manager', $filterManager);

        $filterConfig = new FilterConfig($container, $framework, new FilterSession($framework, $session), new Connection([], new Driver()));
        $container->set('huh.filter.config', $filterConfig);

        $fs = new Filesystem();
        $fs->mkdir($this->getTempDir());

        $kernel = $this->mockAdapter(['getCacheDir', 'isDebug']);
        $kernel->method('getCacheDir')->willReturn($this->getTempDir());
        $kernel->method('isDebug')->willReturn(false);
        $container->set('kernel', $kernel);

        $dcaAdapter = $this->mockAdapter(['getFields']);
        $dcaAdapter->method('getFields')->willReturn(['success']);
        $container->set('huh.utils.dca', $dcaAdapter);

        System::setContainer($container);

        $choiceUtils = new FieldChoice($framework);
        $container->set('huh.utils.choice.field', $choiceUtils);

        $filterConfigElementUtil = new FilterConfigElementUtil($framework);

        $choices = $filterConfigElementUtil->getFields($this->getDataContainerMock());

        $this->assertNotEmpty($choices);
        $this->assertContains('success', $choices);
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
