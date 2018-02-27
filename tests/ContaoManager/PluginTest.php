<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\PluginLoader;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;
use PHPUnit\Framework\MockObject\Matcher\InvokedRecorder;

/**
 * Test the plugin class
 * Class PluginTest.
 */
class PluginTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder($this->mockPluginLoader($this->never()), []);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        static::assertInstanceOf(Plugin::class, new Plugin());
    }

    /**
     * Tests the bundle contao invocation.
     */
    public function testGetBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        static::assertCount(1, $bundles);
        static::assertInstanceOf(BundleConfig::class, $bundles[0]);
        static::assertEquals(HeimrichHannotContaoFilterBundle::class, $bundles[0]->getName());
        static::assertEquals([ContaoCoreBundle::class], $bundles[0]->getLoadAfter());
    }

    /**
     * Test extend configuration.
     */
    public function testGetExtensionConfigEnableFormPlugin()
    {
        $plugin = new Plugin();

        $extensionConfigs = $plugin->getExtensionConfig('framework', [[]], $this->container);

        $this->assertNotEmpty($extensionConfigs);
        $this->assertArrayHasKey(0, $extensionConfigs);
        $this->assertArrayHasKey('form', $extensionConfigs[0]);
        $this->assertArrayHasKey('enabled', $extensionConfigs[0]['form']);
        $this->assertTrue($extensionConfigs[0]['form']['enabled']);
    }

    /**
     * Test extend configuration.
     */
    public function testGetExtensionConfigLoadFilterConfig()
    {
        $plugin = new Plugin();

        $extensionConfigs = $plugin->getExtensionConfig('huh_filter', [[]], $this->container);

        $this->assertNotEmpty($extensionConfigs);
        $this->assertArrayHasKey('huh', $extensionConfigs);
        $this->assertArrayHasKey('filter', $extensionConfigs['huh']);

        $this->assertArrayHasKey('types', $extensionConfigs['huh']['filter']);
        $this->assertNotEmpty($extensionConfigs['huh']['filter']['types']);

        $this->assertContains(['name' => 'text', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TextType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'text_concat', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TextConcatType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'textarea', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TextareaType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'email', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\EmailType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'integer', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\IntegerType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'money', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\MoneyType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'number', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\NumberType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'password', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\PasswordType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'percent', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\PercentType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'search', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\SearchType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'url', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\UrlType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'range', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\RangeType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'tel', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TelType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'color', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ColorType', 'type' => 'text'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'choice', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ChoiceType', 'type' => 'choice'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'country', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\CountryType', 'type' => 'choice'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'language', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\LanguageType', 'type' => 'choice'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'locale', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\LocaleType', 'type' => 'choice'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'parent', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ParentType', 'type' => 'choice'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'button', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ButtonType', 'type' => 'button'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'reset', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\ResetType', 'type' => 'button'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'submit', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\SubmitType', 'type' => 'button'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'hidden', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\HiddenType', 'type' => 'other'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'checkbox', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\CheckboxType', 'type' => 'other'], $extensionConfigs['huh']['filter']['types']);
        $this->assertContains(['name' => 'radio', 'class' => 'HeimrichHannot\FilterBundle\Filter\Type\RadioType', 'type' => 'other'], $extensionConfigs['huh']['filter']['types']);

        $this->assertArrayHasKey('templates', $extensionConfigs['huh']['filter']);
        $this->assertNotEmpty($extensionConfigs['huh']['filter']['templates']);

        $this->assertContains(['name' => 'form_div_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_div_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
        $this->assertContains(['name' => 'form_table_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_table_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
        $this->assertContains(['name' => 'bootstrap_3_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
        $this->assertContains(['name' => 'bootstrap_3_horizontal_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_3_horizontal_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
        $this->assertContains(['name' => 'bootstrap_4_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
        $this->assertContains(['name' => 'bootstrap_4_horizontal_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_horizontal_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
        $this->assertContains(['name' => 'foundation_5_layout', 'template' => '@HeimrichHannotContaoFilter/filter/filter_form_foundation_5_layout.html.twig'], $extensionConfigs['huh']['filter']['templates']);
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
