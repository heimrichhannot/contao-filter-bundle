<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Config;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Session\FilterSession;
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
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $config = $this->getFilterConfig();
        $this->assertInstanceOf(FilterConfig::class, $config);
    }

    /**
     * Tests init.
     */
    public function testInit()
    {
        $config = $this->getFilterConfig();

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
        $config = $this->getFilterConfig();
        $config->buildForm();

        $this->assertNull($config->getBuilder(), $config);
    }

    /**
     * Test form builder.
     */
    public function testBuildFormWithoutElementsAndAction()
    {
        $filter = [
            'id' => 1,
            'dataContainer' => 'tl_member',
            'name' => 'test_form',
            'method' => 'GET',
        ];

        $config = $this->getFilterConfig();

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
            'id' => 1,
            'dataContainer' => 'tl_member',
            'name' => 'test_form',
            'method' => 'GET',
            'action' => '{{env::path}}',
        ];

        $config = $this->getFilterConfig();
        $config->init('test', $filter);
        $config->buildForm();

        $this->assertNotNull($config->getBuilder(), $config);
        $this->assertInstanceOf(FormBuilder::class, $config->getBuilder());
        $this->assertSame('/_filter/submit/1', $config->getBuilder()->getAction());
    }

    private function getContainerMock(ContainerBuilder $container = null)
    {
        if (!$container) {
            $container = $this->mockContainer();
        }
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $container->set('request_stack', $requestStack);

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('filter_frontend_submit', $this->anything())->willReturnCallback(function ($route, $params = []) {
            return '/_filter/submit/1';
        });
        $container->set('router', $router);

        System::setContainer($container);

        return $container;
    }

    private function getFilterConfig(ContainerBuilder $container = null)
    {
        $container = $this->getContainerMock($container);
        $framework = $this->mockContaoFramework([]);
        $connection = new Connection([], new Driver());
        $session = new MockArraySessionStorage();

        return new FilterConfig($container, $framework, new FilterSession($framework, new Session($session)), $connection);
    }
}
