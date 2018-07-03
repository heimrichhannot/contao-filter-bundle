<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\ElementChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class ElementChoiceTest extends ContaoTestCase
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
        $instance  = new ElementChoice($framework);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Choice\ElementChoice', $instance);
    }

    /**
     * Tests the element collection with valid context and elements.
     */
    public function testCollectElementsWithContextAndElements()
    {
        $this->container->set('kernel', $this->kernel);

        $filterConfigDateElement = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'id'    => 1,
                'pid'   => 1,
                'type'  => 'date',
                'title' => 'testDate',
                'name'  => 'testDate',
            ]
        );

        $filterConfigTimeElement = $this->mockClassWithProperties(
            FilterConfigElementModel::class,
            [
                'id'    => 2,
                'pid'   => 1,
                'type'  => 'time',
                'title' => 'testTime',
                'name'  => 'testTime',
            ]
        );

        $filterConfigElementAdapter = $this->mockAdapter(['findPublishedByPidAndTypes']);
        $filterConfigElementAdapter->method('findPublishedByPidAndTypes')->willReturn(
            [
                $filterConfigDateElement,
                $filterConfigTimeElement,
            ]
        );

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $filterConfigElementAdapter]);

        System::setContainer($this->container);

        $context = ['pid' => 1, 'types' => ['date']];

        $instance = new ElementChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertNotEmpty($choices);
        $this->assertCount(2, $choices);
        $this->assertSame('testDate [date]', $choices[1]);
        $this->assertSame('testTime [time]', $choices[2]);
    }

    /**
     * Tests the element collection with invalid context.
     */
    public function testCollectWithNoElements()
    {
        $this->container->set('kernel', $this->kernel);

        $filterConfigElementAdapter = $this->mockAdapter(['findPublishedByPidAndTypes']);
        $filterConfigElementAdapter->method('findPublishedByPidAndTypes')->willReturn(null);

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $filterConfigElementAdapter]);

        System::setContainer($this->container);

        $context = ['pid' => 1, 'types' => ['date']];

        $instance = new ElementChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the element collection without model adapter.
     */
    public function testCollectWithNoAdapter()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['pid' => 1, 'types' => ['date']];

        $instance = new ElementChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the element collection with invalid pid.
     */
    public function testCollectWithInvalidPid()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['pid' => 0];

        $instance = new ElementChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the element collection with invalid context.
     */
    public function testCollectWithInvalidContext()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = ['pid' => []];

        $instance = new ElementChoice($framework);
        $choices  = $instance->getChoices($context);

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the element collection without filter and element.
     */
    public function testCollectWithoutFilterAndPidAndTypes()
    {
        $this->container->set('kernel', $this->kernel);

        $framework = $this->mockContaoFramework();

        System::setContainer($this->container);

        $context = [];

        $instance = new ElementChoice($framework);
        $choices  = $instance->getChoices($context);

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
