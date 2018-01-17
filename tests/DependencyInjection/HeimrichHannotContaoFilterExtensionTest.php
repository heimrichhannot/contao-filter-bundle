<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\DependencyInjection;


use HeimrichHannot\FilterBundle\DependencyInjection\HeimrichHannotContaoFilterExtension;
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
        $extension       = new HeimrichHannotContaoFilterExtension();
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
     * Test getAlias
     */
    public function testGetAlias()
    {
        $extension = new HeimrichHannotContaoFilterExtension();
        $this->assertEquals('huh_filter', $extension->getAlias());
    }
}