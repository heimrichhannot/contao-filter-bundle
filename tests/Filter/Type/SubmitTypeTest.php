<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\SubmitType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class SubmitTypeTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    protected function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_test'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql' => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [
            ],
        ];

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql' => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [
            ],
        ];

        $finder = new ResourceFinder([
            $this->getFixturesDir().'/vendor/contao/core-bundle/Resources/contao',
        ]);

        $this->container = $this->mockContainer();
        $this->container->set('contao.resource_finder', $finder);
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->set('translator', new Translator('en'));

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('filter_frontend_submit', $this->anything())->willReturnCallback(function ($route, $params = []) {
            return '/_filter/submit/1';
        });

        $this->container->set('router', $router);

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        $this->container->set('kernel', $this->kernel);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        System::setContainer($this->container);

        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $type = new SubmitType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\SubmitType', $type);
    }

    /**
     * Test getDefaultOperator().
     */
    public function testGetDefaultOperator()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        /** @var FilterConfigElementModel $element */
        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, []);

        $type = new SubmitType($config);

        $this->assertNull($type->getDefaultOperator($element));
    }

    /**
     * Test getDefaultName().
     */
    public function testGetDefaultName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $range = new FilterConfigElementModel();
        $range->name = 'test';

        $type = new SubmitType($config);

        $this->assertSame('submit', $type->getDefaultName($range));
    }

    /**
     * Test buildForm() without name.
     */
    public function testBuildFormWithoutName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'submit',
                        'class' => SubmitType::class,
                        'type' => 'button',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'submit';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertFalse($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\SubmitType::class, $config->getBuilder()->get('submit')->getType()->getInnerType());
    }

    /**
     * Test buildForm() with name.
     */
    public function testBuildFormWithName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'submit',
                        'class' => SubmitType::class,
                        'type' => 'button',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'submit';
        $element->name = 'test';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertFalse($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\SubmitType::class, $config->getBuilder()->get('submit')->getType()->getInnerType());
    }

    /**
     * Test buildForm() with custom label.
     */
    public function testBuildFormWithLabel()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'submit',
                        'class' => SubmitType::class,
                        'type' => 'button',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'submit';
        $element->name = 'test';
        $element->customLabel = true;
        $element->label = 'Button label';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertFalse($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\SubmitType::class, $config->getBuilder()->get('submit')->getType()->getInnerType());
        $this->assertSame('Button label', $config->getBuilder()->get('submit')->getForm()->getConfig()->getOption('label'));
    }

    /**
     * Test buildQuery().
     */
    public function testBuildQuery()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'submit',
                        'class' => SubmitType::class,
                        'type' => 'button',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->id = 2;
        $element->type = 'submit';
        $element->name = 'start';

        $config->init('test', $filter, [$element]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertEmpty($config->getQueryBuilder()->getQueryPart('where'));
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'../..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
