<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\MemberModel;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\ParentType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\UtilsBundle\Choice\ModelInstanceChoice;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class ParentTypeTest extends ContaoTestCase
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
                    'keys' => [],
                ],
            ],
            'fields' => [],
        ];

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql' => [
                    'keys' => [],
                ],
            ],
            'fields' => [],
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
        $router->method('generate')->with('filter_frontend_submit', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            return '/_filter/submit/1';
        }));

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

        $type = new ParentType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\ParentType', $type);
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

        $type = new ParentType($config);

        $this->assertSame(DatabaseUtil::OPERATOR_EQUAL, $type->getDefaultOperator($element));
    }

    /**
     * Test getChoices() without a dataContainer.
     */
    public function testGetChoicesWithoutDataContainer()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        /** @var FilterConfigElementModel $element */
        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, []);

        $filter = ['name' => 'test'];
        $config->init('test', $filter, [$element]);

        $type = new ParentType($config);

        $this->assertEmpty($type->getChoices($element));
    }

    /**
     * Test getChoices() for tl_member table.
     */
    public function testGetChoicesForMemberTable()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $modelInstances = $this->mockClassWithProperties(MemberModel::class, ['id' => 12, 'title' => 'test']);
        $collection = $this->mockClassWithProperties(Collection::class, ['id' => 12, 'title' => 'test']);
        $collection->method('next')->willReturn($modelInstances, $modelInstances);
        $modelUtilAdapter = $this->mockAdapter(['findModelInstancesBy']);
        $modelUtilAdapter->method('findModelInstancesBy')->willReturn($collection);
        $this->container->set('huh.utils.model', $modelUtilAdapter);

        $this->container->set('huh.utils.choice.model_instance', new ModelInstanceChoice($framework));

        System::setContainer($this->container);

        $element = new FilterConfigElementModel();
        $element->columns = [];
        $element->values = [];

        $GLOBALS['TL_DCA']['tl_member_group']['fields']['title'] = [];

        $filter = ['name' => 'test', 'dataContainer' => 'tl_member'];
        $config->init('test', $filter, [$element]);

        $type = new ParentType($config);

        $this->assertSame(['test [ID: 12]' => 12], $type->getChoices($element));
    }

    /**
     * Test getChoices() by dca foreignKey.
     */
    public function testGetChoicesByForeignKey()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $modelInstances = $this->mockClassWithProperties(PageModel::class, ['id' => 8, 'title' => 'Test page title']);
        $collection = $this->mockClassWithProperties(Collection::class, ['id' => 8, 'title' => 'Test page title']);
        $collection->method('next')->willReturn($modelInstances, $modelInstances);
        $modelUtilAdapter = $this->mockAdapter(['findModelInstancesBy']);
        $modelUtilAdapter->method('findModelInstancesBy')->willReturn($collection);
        $this->container->set('huh.utils.model', $modelUtilAdapter);

        $this->container->set('huh.utils.choice.model_instance', new ModelInstanceChoice($framework));

        System::setContainer($this->container);

        $element = new FilterConfigElementModel();
        $element->columns = [];
        $element->values = [];

        $GLOBALS['TL_DCA']['tl_article']['fields']['pid'] = [
            'foreignKey' => 'tl_page.title',
        ];

        $GLOBALS['TL_DCA']['tl_page']['fields']['title'] = [];

        $filter = ['name' => 'test', 'dataContainer' => 'tl_article'];
        $config->init('test', $filter, [$element]);

        $type = new ParentType($config);

        $this->assertSame(['Test page title [ID: 8]' => 8], $type->getChoices($element));
    }

    /**
     * Test buildForm() with field name.
     */
    public function testBuildFormWithFieldName()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'parent',
                        'class' => ParentType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'parent';
        $element->field = 'test';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, $config->getBuilder()->get('test')->getType()->getInnerType());
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'../..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
