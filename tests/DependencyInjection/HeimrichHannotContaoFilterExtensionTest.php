<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\DependencyInjection;

use HeimrichHannot\FilterBundle\Choice\CountryChoice;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\Choice\LanguageChoice;
use HeimrichHannot\FilterBundle\Choice\LocaleChoice;
use HeimrichHannot\FilterBundle\Choice\TemplateChoice;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\DependencyInjection\HeimrichHannotContaoFilterExtension;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class HeimrichHannotContaoFilterExtensionTest extends TestCase
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
        $this->container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new HeimrichHannotContaoFilterExtension();
        $extension->load([], $this->container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new HeimrichHannotContaoFilterExtension();
        $this->assertInstanceOf(HeimrichHannotContaoFilterExtension::class, $extension);
    }

    /**
     * Test getAlias.
     */
    public function testGetAlias()
    {
        $extension = new HeimrichHannotContaoFilterExtension();
        $this->assertSame('huh_filter', $extension->getAlias());
    }

    /**
     * Tests the huh.filter.manager service.
     */
    public function testRegistersTheFilterManager()
    {
        $this->assertTrue($this->container->has('huh.filter.manager'));

        $definition = $this->container->getDefinition('huh.filter.manager');

        $this->assertSame(FilterManager::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
        $this->assertSame('huh.filter.session', (string) $definition->getArgument(1));
    }

    /**
     * Tests the huh.filter.session service.
     */
    public function testRegistersTheFilterSession()
    {
        $this->assertTrue($this->container->has('huh.filter.session'));

        $definition = $this->container->getDefinition('huh.filter.session');

        $this->assertSame(FilterSession::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
        $this->assertSame('session', (string) $definition->getArgument(1));
    }

    /**
     * Tests the huh.filter.config service.
     */
    public function testRegistersTheFilterConfig()
    {
        $this->assertTrue($this->container->has('huh.filter.config'));

        $definition = $this->container->getDefinition('huh.filter.config');

        $this->assertSame(FilterConfig::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(1));
        $this->assertSame('huh.filter.session', (string) $definition->getArgument(2));
    }

    /**
     * Tests the huh.filter.choice.template service.
     */
    public function testRegistersTheFilterChoiceTemplate()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.template'));

        $definition = $this->container->getDefinition('huh.filter.choice.template');

        $this->assertSame(TemplateChoice::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }

    /**
     * Tests the huh.filter.choice.type service.
     */
    public function testRegistersTheFilterChoiceType()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.type'));

        $definition = $this->container->getDefinition('huh.filter.choice.type');

        $this->assertSame(TypeChoice::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }

    /**
     * Tests the huh.filter.choice.field_options service.
     */
    public function testRegistersTheFilterChoiceFieldOptions()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.field_options'));

        $definition = $this->container->getDefinition('huh.filter.choice.field_options');

        $this->assertSame(FieldOptionsChoice::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }

    /**
     * Tests the huh.filter.choice.country service.
     */
    public function testRegistersTheFilterChoiceCountry()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.country'));

        $definition = $this->container->getDefinition('huh.filter.choice.country');

        $this->assertSame(CountryChoice::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }

    /**
     * Tests the huh.filter.choice.language service.
     */
    public function testRegistersTheFilterChoiceLanguage()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.language'));

        $definition = $this->container->getDefinition('huh.filter.choice.language');

        $this->assertSame(LanguageChoice::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }

    /**
     * Tests the huh.filter.choice.locale service.
     */
    public function testRegistersTheFilterChoiceLocale()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.locale'));

        $definition = $this->container->getDefinition('huh.filter.choice.locale');

        $this->assertSame(LocaleChoice::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
    }
}
