<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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