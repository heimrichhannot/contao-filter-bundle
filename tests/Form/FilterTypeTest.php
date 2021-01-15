<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Form;

use HeimrichHannot\FilterBundle\Exception\MissingFilterConfigException;
use HeimrichHannot\FilterBundle\Form\FilterType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

class FilterTypeTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $instance = new FilterType();

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Form\FilterType', $instance);
    }

    /**
     * Test buildForm() without filter config.
     */
    public function testBuildFormWithoutFilterConfig()
    {
        $factory = Forms::createFormFactoryBuilder()->addExtensions([])->getFormFactory();

        $this->expectException(MissingFilterConfigException::class);

        $factory->createNamedBuilder('test', FilterType::class, [], []);
    }
}
