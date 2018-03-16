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
use HeimrichHannot\FilterBundle\Choice\TypeChoice;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\Filter\Type\UrlType;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Translator;

class UrlTypeTest extends ContaoTestCase
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

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE']    = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_test'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql'           => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [

            ]
        ];

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'config' => [
                'dataContainer' => 'Table',
                'sql'           => [
                    'keys' => [
                    ],
                ],
            ],
            'fields' => [

            ]
        ];

        $finder = new ResourceFinder([
            $this->getFixturesDir() . '/vendor/contao/core-bundle/Resources/contao',
        ]);

        $this->container = $this->mockContainer();
        $this->container->set('contao.resource_finder', $finder);
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->set('translator', new Translator('en'));

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
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $type = new UrlType($config);

        $this->assertInstanceOf('HeimrichHannot\FilterBundle\Filter\Type\UrlType', $type);
    }

    /**
     * Test getDefaultOperator()
     */
    public function testGetDefaultOperator()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        /** @var FilterConfigElementModel $element */
        $element = $this->mockClassWithProperties(FilterConfigElementModel::class, []);

        $type = new UrlType($config);

        $this->assertEquals(DatabaseUtil::OPERATOR_LIKE, $type->getDefaultOperator($element));
    }

    /**
     * Test buildForm() with field name
     */
    public function testBuildFormWithFieldName()
    {
        $framework = $this->mockContaoFramework();
        $session   = new MockArraySessionStorage();

        $queryBuilder = new FilterQueryBuilder($framework, new Connection([], new Driver()));
        $config       = new FilterConfig($framework, new FilterSession($framework, new Session($session)), $queryBuilder);

        $this->container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'url',
                        'class' => UrlType::class,
                        'type'  => 'text'
                    ]
                ]
            ]
        ]);

        $this->container->set('huh.filter.choice.type', new TypeChoice($framework));
        System::setContainer($this->container);

        $filter = ['name' => 'test', 'dataContainer' => 'tl_test'];

        $element        = new FilterConfigElementModel();
        $element->type  = 'url';
        $element->field = 'test';

        $config->init('test', $filter, [$element]);
        $config->buildForm();

        $this->assertEquals(2, $config->getBuilder()->count()); // f_id element always exists
        $this->assertTrue($config->getBuilder()->has('test'));
        $this->assertInstanceOf(\Symfony\Component\Form\Extension\Core\Type\UrlType::class, $config->getBuilder()->get('test')->getType()->getInnerType());
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../..' . DIRECTORY_SEPARATOR . 'Fixtures';
    }
}
