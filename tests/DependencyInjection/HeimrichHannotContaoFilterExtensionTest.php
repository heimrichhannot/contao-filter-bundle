<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\DependencyInjection;

use HeimrichHannot\FilterBundle\Choice\CountryChoice;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\Choice\LanguageChoice;
use HeimrichHannot\FilterBundle\Choice\LocaleChoice;
use HeimrichHannot\FilterBundle\Choice\ParentChoice;
use HeimrichHannot\FilterBundle\Choice\TemplateChoice;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\DependencyInjection\HeimrichHannotContaoFilterExtension;
use HeimrichHannot\FilterBundle\Entity\FilterSession;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Registry\FilterRegistry;
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
     * Tests the huh.filter.registry service.
     */
    public function testRegistersTheFilterRegistry()
    {
        $this->assertTrue($this->container->has('huh.filter.registry'));

        $definition = $this->container->getDefinition('huh.filter.registry');

        $this->assertSame(FilterRegistry::class, $definition->getClass());
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
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
        $this->assertSame('huh.filter.session', (string) $definition->getArgument(1));
    }

    /**
     * Tests the huh.filter.query_builder service.
     */
    public function testRegistersTheFilterQueryBuilder()
    {
        $this->assertTrue($this->container->has('huh.filter.query_builder'));

        $definition = $this->container->getDefinition('huh.filter.query_builder');

        $this->assertSame(FilterQueryBuilder::class, $definition->getClass());
        $this->assertSame('contao.framework', (string) $definition->getArgument(0));
        $this->assertSame('doctrine.dbal.default_connection', (string) $definition->getArgument(1));
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
     * Tests the huh.filter.choice.parent service.
     */
    public function testRegistersTheFilterChoiceParent()
    {
        $this->assertTrue($this->container->has('huh.filter.choice.parent'));

        $definition = $this->container->getDefinition('huh.filter.choice.parent');

        $this->assertSame(ParentChoice::class, $definition->getClass());
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
