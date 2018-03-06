<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Entity\FilterSession;
use HeimrichHannot\FilterBundle\Filter\Type\DateRangeType;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Translation\Translator;

class DateRangeTypeTest extends ContaoTestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $container = $this->mockContainer();
        $container->set('translator', new Translator('en'));
        System::setContainer($container);

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $instance = new DateRangeType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\DateRangeType', $instance);
    }
}
