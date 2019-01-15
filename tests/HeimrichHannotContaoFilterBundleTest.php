<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
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
