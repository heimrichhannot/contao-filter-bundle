<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\Choice;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\DataContainer;
use Contao\ManagerPlugin\PluginLoader;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Contao\ThemeModel;
use HeimrichHannot\FilterBundle\Choice\TemplateChoice;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\Kernel;

class TemplateChoiceTest extends ContaoTestCase
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

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);

        $this->kernel = $this->createMock(Kernel::class);
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
        $themeModelAdapter = $this->mockAdapter(['findAll']);
        $themeModelAdapter->method('findAll')->willReturn(null);

        $finder = new ResourceFinder(
            ([
                $this->getFixturesDir(),
            ])
        );

        $this->container->set('contao.resource_finder', $finder);

        $framework = $this->mockContaoFramework([ThemeModel::class => $themeModelAdapter]);
        $this->container->setParameter('huh.filter', $this->config);
        $this->container->set('huh.utils.template', new TemplateUtil($framework));
        $this->container->set('huh.utils.container', new ContainerUtil($framework, $this->createMock(FileLocator::class)));
        $this->container->set('huh.utils.choice.twig_template', new TwigTemplateChoice($framework));
        $this->container->set('kernel', $this->kernel);
        $this->container->setParameter('kernel.project_dir', $this->getFixturesDir());

        System::setContainer($this->container);

        $instance = new TemplateChoice($framework);
        $choices = $instance->getChoices();

        $this->assertNotEmpty($choices);
        $this->assertArrayHasKey('form_div_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_div_layout.html.twig (Yaml)', $choices['form_div_layout']);
        $this->assertArrayHasKey('form_table_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_table_layout.html.twig (Yaml)', $choices['form_table_layout']);
        $this->assertArrayHasKey('bootstrap_3_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_layout.html.twig (Yaml)', $choices['bootstrap_3_layout']);
        $this->assertArrayHasKey('bootstrap_3_horizontal_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_horizontal_layout.html.twig (Yaml)', $choices['bootstrap_3_horizontal_layout']);
        $this->assertArrayHasKey('bootstrap_4_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_layout.html.twig (Yaml)', $choices['bootstrap_4_layout']);
        $this->assertArrayHasKey('bootstrap_4_horizontal_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_horizontal_layout.html.twig (Yaml)', $choices['bootstrap_4_horizontal_layout']);
        $this->assertArrayHasKey('foundation_5_layout', $choices);
        $this->assertSame('@HeimrichHannotContaoFilter/filter/filter_form_foundation_5_layout.html.twig (Yaml)', $choices['foundation_5_layout']);
        $this->assertArrayHasKey('filter_test', $choices);
        $this->assertSame('filter_test.html.twig (../../Fixtures/templates)', $choices['filter_test']);
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

        $filterConfigElementModelAdapter = $this->mockAdapter(['findById']);
        $filterConfigElementModelAdapter->method('findById')->willReturn(
            [
                null,
            ]
        );

        $filterConfigModelAdapter = $this->mockAdapter(['findById']);
        $filterConfigModelAdapter->method('findById')->willReturn(
            [
                null,
            ]
        );

        $framework = $this->mockContaoFramework([FilterConfigElementModel::class => $filterConfigElementModelAdapter, FilterConfigElementModel::class => $filterConfigModelAdapter]);

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
        $this->assertSame('visible', $choices['other'][0]);
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }

    /**
     * Mocks the plugin loader.
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
