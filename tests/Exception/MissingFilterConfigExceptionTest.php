<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Exception;

use HeimrichHannot\FilterBundle\Exception\MissingFilterConfigException;
use PHPUnit\Framework\TestCase;

class MissingFilterConfigExceptionTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        static::assertInstanceOf(MissingFilterConfigException::class, new MissingFilterConfigException());
    }
}
