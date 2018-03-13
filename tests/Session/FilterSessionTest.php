<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\Session;


use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;

class FilterSessionTest extends ContaoTestCase
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

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $instance = new \HeimrichHannot\FilterBundle\Session\FilterSession($this->mockContaoFramework(), new Session(new MockArraySessionStorage()));

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Session\FilterSession', $instance);
    }

    /**
     * Test setData()
     */
    public function testSetData()
    {
        $session = new Session(new MockArraySessionStorage());

        $instance = new \HeimrichHannot\FilterBundle\Session\FilterSession($this->mockContaoFramework(), $session);
        $instance->setData('test', ['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $instance->getData('test'));
    }

    /**
     * Test hasData() with FilterType::FILTER_ID_NAME only
     */
    public function testHasDataWithFilterIdFieldOnly()
    {
        $session = new Session(new MockArraySessionStorage());

        $instance = new \HeimrichHannot\FilterBundle\Session\FilterSession($this->mockContaoFramework(), $session);
        $instance->setData('test', [FilterType::FILTER_ID_NAME => 'test_1']);

        $this->assertFalse($instance->hasData('test'));
    }

    /**
     * Test hasData() with FilterType::FILTER_ID_NAME set and data
     */
    public function testHasData()
    {
        $session = new Session(new MockArraySessionStorage());

        $instance = new \HeimrichHannot\FilterBundle\Session\FilterSession($this->mockContaoFramework(), $session);
        $instance->setData('test', ['foo' => 'bar', FilterType::FILTER_ID_NAME => 'test_1']);

        $this->assertTrue($instance->hasData('test'));
    }

    /**
     * Test reset()
     */
    public function testReset()
    {
        $session = new Session(new MockArraySessionStorage());

        $instance = new \HeimrichHannot\FilterBundle\Session\FilterSession($this->mockContaoFramework(), $session);
        $instance->setData('test', ['foo' => 'bar', FilterType::FILTER_ID_NAME => 'test_1']);

        $this->assertEquals( ['foo' => 'bar', FilterType::FILTER_ID_NAME => 'test_1'], $instance->getData('test'));

        $instance->reset('test');

        $this->assertEmpty($instance->getData('test'));
    }
}