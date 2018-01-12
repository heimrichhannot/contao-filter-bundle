<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests;

use HeimrichHannot\FilterBundle\HeimrichHannotContaoFilterBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoFilterBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoFilterBundle();

        $this->assertInstanceOf(HeimrichHannotContaoFilterBundle::class, $bundle);
    }

    /**
     * Tests the getContainerExtension() method.
     */
    public function testReturnsTheContainerExtension()
    {
        $bundle = new HeimrichHannotContaoFilterBundle();

        $this->assertInstanceOf(
            'HeimrichHannot\FilterBundle\DependencyInjection\HeimrichHannotContaoFilterExtension',
            $bundle->getContainerExtension()
        );
    }
}