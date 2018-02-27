<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\DataContainer;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Choice\TemplateChoice;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TemplateChoiceTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var ContaoKernel
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

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);

        $this->kernel = $this->createMock(ContaoKernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        $plugin = new Plugin();

        $containerBuilder = new \Contao\ManagerPlugin\Config\ContainerBuilder($this->mockPluginLoader($this->never()), []);

        $config = $plugin->getExtensionConfig('huh_filter', [[]], $containerBuilder);
        $this->config['filter'] = $config['huh']['filter'];
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
        $instance = new TemplateChoice($framework);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Choice\TemplateChoice', $instance);
    }

    /**
     * Tests the type collection without templates.
     */
    public function testCollectWithoutTypes()
    {
        $this->container->set('kernel', $this->kernel);
        $this->container->setParameter('huh.filter', ['filter' => []]);

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $instance = new TemplateChoice($framework);
        $choices = $instance->getChoices();

        System::setContainer($this->container);

        $this->assertEmpty($choices);
    }

    /**
     * Tests the type collection with types without context.
     */
    public function testCollectWithExistingTypesWithoutContext()
    {
        $this->container->set('kernel', $this->kernel);
        $this->container->setParameter('huh.filter', $this->config);

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $instance = new TemplateChoice($framework);
        $choices = $instance->getChoices();

        $this->assertNotEmpty($choices);
        $this->assertArrayHasKey('form_div_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_div_layout.html.twig', $choices['form_div_layout']);
        $this->assertArrayHasKey('form_table_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_table_layout.html.twig', $choices['form_table_layout']);
        $this->assertArrayHasKey('bootstrap_3_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_layout.html.twig', $choices['bootstrap_3_layout']);
        $this->assertArrayHasKey('bootstrap_3_horizontal_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_horizontal_layout.html.twig', $choices['bootstrap_3_horizontal_layout']);
        $this->assertArrayHasKey('bootstrap_4_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_layout.html.twig', $choices['bootstrap_4_layout']);
        $this->assertArrayHasKey('bootstrap_4_horizontal_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_horizontal_layout.html.twig', $choices['bootstrap_4_horizontal_layout']);
        $this->assertArrayHasKey('foundation_5_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_foundation_5_layout.html.twig', $choices['foundation_5_layout']);
    }

    /**
     * Tests the type collection with existing types but missing text type class.
     */
    public function testCollectWithExistingTypeWithMissingClassWithoutContext()
    {
        $config = $this->config;
        $config['filter']['types'][0]['class'] = '_NonExistingNamespace\NonExistingClass';

        $this->container->set('kernel', $this->kernel);
        $this->container->setParameter('huh.filter', $config);

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $instance = new TypeChoice($framework);
        $choices = $instance->getChoices();

        $this->assertNotEmpty($choices);
        $this->assertArrayNotHasKey('text', $choices);
    }

    /**
     * Tests the type collection with existing types and data container context, should return opt groups.
     */
    public function testCollectWithExistingTypesWithDataContainerContext()
    {
        $this->container->set('kernel', $this->kernel);
        $this->container->setParameter('huh.filter', $this->config);

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $instance = new TypeChoice($framework);

        $dataContainerMock = $this->createMock(DataContainer::class);

        $choices = $instance->getChoices($dataContainerMock);

        $this->assertNotEmpty($choices);
        $this->assertArrayHasKey('text', $choices);
        $this->assertSame('text', $choices['text'][0]);
        $this->assertArrayHasKey('choice', $choices);
        $this->assertSame('choice', $choices['choice'][0]);
        $this->assertArrayHasKey('button', $choices);
        $this->assertSame('button', $choices['button'][0]);
        $this->assertArrayHasKey('other', $choices);
        $this->assertSame('hidden', $choices['other'][0]);
    }

    /**
     * Mocks the plugin loader.
     *
     * @param InvokedRecorder $expects
     * @param array           $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(InvokedRecorder $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
