<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Config;

use Contao\InsertTags;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class FilterConfigTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;


    protected function setUp()
    {
        parent::setUp();

        $request = new Request();

        $this->container = $this->mockContainer();
        $requestStack    = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('filter_frontend_submit', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            return '/_filter/submit/1';
        }));

        $this->container->set('router', $router);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $framework = $this->mockContaoFramework([]);
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));

        $config = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Config\FilterConfig', $config);
    }

    /**
     * Tests init.
     */
    public function testInit()
    {
        $framework = $this->mockContaoFramework([]);
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);
        $config->init('test', []);

        $this->assertSame('test', $config->getSessionKey());
        $this->assertSame([], $config->getFilter());
        $this->assertNull($config->getElements());
    }

    /**
     * Test form builder.
     */
    public function testBuildFormWithNotFilter()
    {
        $framework = $this->mockContaoFramework([]);
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);
        $config->buildForm();

        $this->assertNull($config->getBuilder(), $config);
    }

    /**
     * Test form builder.
     */
    public function testBuildFormWithoutElementsAndAction()
    {
        $filter = [
            'id'            => 1,
            'dataContainer' => 'tl_member',
            'name'          => 'test_form',
            'method'        => 'GET',
        ];

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework([]);
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);
        $config->init('test', $filter);
        $config->buildForm();

        $this->assertNotNull($config->getBuilder(), $config);
        $this->assertInstanceOf(FormBuilder::class, $config->getBuilder());
    }

    /**
     * Test form builder.
     */
    public function testBuildFormWithInsertTagActionAndWithoutElements()
    {
        $filter = [
            'id'            => 1,
            'dataContainer' => 'tl_member',
            'name'          => 'test_form',
            'method'        => 'GET',
            'action'        => '{{env::path}}',
        ];

        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);
        $config->init('test', $filter);
        $config->buildForm();

        $this->assertNotNull($config->getBuilder(), $config);
        $this->assertInstanceOf(FormBuilder::class, $config->getBuilder());
        $this->assertSame('/_filter/submit/1', $config->getBuilder()->getAction());
    }
}
