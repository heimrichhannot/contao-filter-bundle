<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Filter\Type;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use HeimrichHannot\FilterBundle\Choice\CountryChoice;
use HeimrichHannot\FilterBundle\Choice\FieldOptionsChoice;
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\CountryType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class CountryTypeTest extends ContaoTestCase
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

        $type = new CountryType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\CountryType', $type);
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

        $type = new CountryType($config);

        $this->assertSame(DatabaseUtil::OPERATOR_EQUAL, $type->getDefaultOperator($element));
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

        $type = new CountryType($config);

        $this->assertNull($type->getDefaultName($range)); // customName must be active
    }

    /**
     * Test getDefaultName().
     */
    public function testGetChoices()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $element = new FilterConfigElementModel();
        $element->type = 'choice';

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $config->init('test', $filter, [$element]);

        $fieldOptionsChoice = new FieldOptionsChoice($this->mockContaoFramework());
        $this->container->set('huh.filter.choice.field_options', $fieldOptionsChoice);
        System::setContainer($this->container);

        $type = new CountryType($config);

        $this->assertEmpty($type->getChoices($element));
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
                        'name' => 'country',
                        'class' => CountryType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'choice';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(2, $config->getBuilder()->count());  // f_id and f_ref element always exists
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
                        'name' => 'country',
                        'class' => CountryType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        $this->container->set('huh.filter.choice.country', new CountryChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'country';
        $element->field = 'test';
        $element->customCountries = true;
        $element->countries = ['DE', 'CA'];

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\CountryType::class, $config->getBuilder()->get('test')->getType()->getInnerType());
        $this->assertNotEmpty($config->getBuilder()->get('test')->getForm()->getConfig()->getOption('choices'));
        $this->assertSame(['Canada' => 'CA', 'Germany' => 'DE'], $config->getBuilder()->get('test')->getForm()->getConfig()->getOption('choices'));
    }

    /**
     * Test buildForm() with non array default values.
     */
    public function testBuildFormWithNonArrayDefaultValues()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'country',
                        'class' => CountryType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->type = 'country';
        $element->field = 'test';
        $element->addDefaultValue = true;
        $element->defaultValue = '123';
        $element->addDefaultValue = true;
        $element->multiple = true;

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\CountryType::class, $config->getBuilder()->get('test')->getType()->getInnerType());
        $this->assertSame(['123'], $config->getBuilder()->get('test')->getForm()->getConfig()->getOption('data'));
    }

    /**
     * Test buildForm() with field name.
     */
    public function testBuildFormWithPlaceholder()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'country',
                        'class' => CountryType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $translator = new Translator('en');
        $translator->getCatalogue()->add(['message.test_placholder' => 'test placeholder']);
        $this->container->set('translator', $translator);

        $element = new FilterConfigElementModel();
        $element->type = 'country';
        $element->field = 'test';
        $element->addPlaceholder = true;
        $element->placeholder = 'message.test_placholder';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertSame(3, $config->getBuilder()->count());  // f_id and f_ref element always exists
        $this->assertTrue($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\CountryType::class, $config->getBuilder()->get('test')->getType()->getInnerType());
        $this->assertSame('test placeholder', $config->getBuilder()->get('test')->getForm()->getConfig()->getOption('placeholder'));
        $this->assertSame('test placeholder', $config->getBuilder()->get('test')->getForm()->getConfig()->getOption('attr')['data-placeholder']);
        $this->assertArrayNotHasKey('placeholder', $config->getBuilder()->get('test')->getForm()->getConfig()->getOption('attr'));
    }

    /**
     * Test buildQuery() without dca field.
     */
    public function testBuildQueryWithoutDcaField()
    {
        $framework = $this->mockContaoFramework();
        $session = new MockArraySessionStorage();

        $config = new FilterConfig($this->container, $framework, new FilterSession($framework, new Session($session)), new Connection([], new Driver()));

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name' => 'country',
                        'class' => CountryType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element = new FilterConfigElementModel();
        $element->id = 2;
        $element->type = 'country';
        $element->name = 'test';
        $element->customName = true;

        $config->init('test', $filter, [$element]);
        $config->initQueryBuilder();

        $this->assertEmpty($config->getQueryBuilder()->getParameters());
        $this->assertEmpty($config->getQueryBuilder()->getQueryPart('where'));
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
                        'name' => 'country',
                        'class' => CountryType::class,
                        'type' => 'choice',
                    ],
                ],
            ],
        ]);

        $GLOBALS['TL_DCA']['tl_test']['fields']['test'] = [
            'inputType' => 'select',
        ];

        $this->container->set('huh.utils.database', new DatabaseUtil($framework));
        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        $this->container->set('huh.filter.choice.country', new CountryChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $element = new FilterConfigElementModel();
        $element->id = 2;
        $element->type = 'country';
        $element->field = 'test';

        $config->init('test', $filter, [$element]);
        $config->setData(['test' => 1]);
        $config->initQueryBuilder();

        $this->assertNotEmpty($config->getQueryBuilder()->getParameters());
        $this->assertNotEmpty($config->getQueryBuilder()->getQueryPart('where'));
        $this->assertSame('SELECT  FROM tl_test WHERE test = :test', $config->getQueryBuilder()->getSQL());
        $this->assertSame([':test' => '1'], $config->getQueryBuilder()->getParameters());
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'../..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}
