<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\Model;


use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\HttpKernel\Kernel;

class FilterConfigElementModelTest extends ContaoTestCase
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
        $instance = new \HeimrichHannot\FilterBundle\Model\FilterConfigElementModel();

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Model\FilterConfigElementModel', $instance);
    }
}