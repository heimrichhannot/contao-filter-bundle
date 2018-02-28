<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\Utils;

use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Entity\FilterSession;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Registry\FilterRegistry;
use HeimrichHannot\FilterBundle\Util\FilterConfigElementHelper;
use HeimrichHannot\UtilsBundle\Choice\FieldChoice;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FilterConfigElementHelperTest extends ContaoTestCase
{
    /**
     * Tests getFields() with no existing tl_filter_config_element model
     */
    public function testGetFieldsWithoutConfigElementModel()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn('');

        $framework = $this->mockContaoFramework([Model::class => $modelAdapter]);

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);
        System::setContainer($container);

        $this->assertEmpty(FilterConfigElementHelper::getFields($this->getDataContainerMock()));
    }

    /**
     * Tests getFields() if no models exists
     */
    public function testGetFieldsWithoutFilterConfig()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 2];
        $filterConfigElementModel      = $this->mockClassWithProperties(FilterConfigModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test'];
        $filterConfigModel           = $this->mockClassWithProperties(FilterConfigModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $framework = $this->mockContaoFramework(
            [
                Model::class                    => $modelAdapter,
                FilterConfigModel::class        => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $containerUtil = new ContainerUtil($framework);
        $container->set('huh.utils.container', $containerUtil);

        $session        = new Session(new MockArraySessionStorage());
        $filterSession  = new FilterSession($framework, $session);
        $filterRegistry = new FilterRegistry($framework, $filterSession);
        $container->set('huh.filter.registry', $filterRegistry);

        $filterConfig = new FilterConfig($framework, $filterSession);
        $container->set('huh.filter.config', $filterConfig);

        System::setContainer($container);

        $this->assertEmpty(FilterConfigElementHelper::getFields($this->getDataContainerMock()));
    }

    /**
     * Tests getFields() if no models exists
     */
    public function testGetFieldsWithExistingChoices()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1];
        $filterConfigElementModel      = $this->mockClassWithProperties(FilterConfigModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $filterConfigModelProperties = ['id' => 1, 'name' => 'test', 'dataContainer' => 'tl_test'];
        $filterConfigModel           = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigModelProperties);
        $filterConfigModel->method('row')->willReturn($filterConfigModelProperties);

        $filterConfigModelAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigModelAdapter->method('findByPk')->willReturn($filterConfigModel);

        $framework = $this->mockContaoFramework(
            [
                Model::class                    => $modelAdapter,
                FilterConfigModel::class        => $filterConfigModelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $requestStack = new RequestStack();
        $container->set('request_stack', $requestStack);

        $containerUtil = new ContainerUtil($framework);
        $container->set('huh.utils.container', $containerUtil);

        $session        = new Session(new MockArraySessionStorage());
        $filterSession  = new FilterSession($framework, $session);
        $filterRegistry = new FilterRegistry($framework, $filterSession);
        $container->set('huh.filter.registry', $filterRegistry);

        $filterConfig = new FilterConfig($framework, $filterSession);
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

        $choices = FilterConfigElementHelper::getFields($this->getDataContainerMock());

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