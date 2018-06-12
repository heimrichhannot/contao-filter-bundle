<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Tests\Module;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\ManagerPlugin\PluginLoader;
use Contao\ModuleModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Contao\ThemeModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use HeimrichHannot\FilterBundle\Config\FilterConfig;
use HeimrichHannot\FilterBundle\ContaoManager\Plugin;
use HeimrichHannot\FilterBundle\Manager\FilterManager;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Module\ModuleFilter;
use HeimrichHannot\FilterBundle\QueryBuilder\FilterQueryBuilder;
use HeimrichHannot\FilterBundle\Session\FilterSession;
use HeimrichHannot\UtilsBundle\Choice\TwigTemplateChoice;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Template\TemplateUtil;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;

class ModuleFilterTest extends ContaoTestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $config;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_module'] = [
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
        $this->container->set('request_stack', new RequestStack());

        $connection = $this->createMock(Connection::class);
        $connection
            ->method('getDatabasePlatform')
            ->willReturn(new MySqlPlatform());

        $connection
            ->expects(!empty($metadata) ? $this->once() : $this->never())
            ->method('getSchemaManager')
            ->willReturn(new MySqlSchemaManager($connection));

        $this->container->set('database_connection', $connection);

        $this->kernel = $this->createMock(Kernel::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        $plugin = new Plugin();

        $containerBuilder = new \Contao\ManagerPlugin\Config\ContainerBuilder($this->mockPluginLoader($this->never()), []);

        $config = $plugin->getExtensionConfig('huh_filter', [[]], $containerBuilder);
        $this->config['filter'] = $config['huh']['filter'];
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        System::setContainer($this->container);

        /** @var ModuleModel $model */
        $model = $this->mockClassWithProperties(ModuleModel::class, ['id' => 1, 'type' => ModuleFilter::TYPE]);

        $module = new ModuleFilter($model);

        $this->assertInstanceOf(ModuleFilter::class, $module);
    }

    /**
     * Tests generate() in back end mode.
     *
     * @runInSeparateProcess
     */
    public function testGenerateInBackEndMode()
    {
        System::setContainer($this->container);

        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'BE');
        }

        $GLOBALS['TL_LANG']['FMD'][ModuleFilter::TYPE][0] = 'Filter';

        $model = new ModuleModel();
        $model->id = 1;
        $model->type = ModuleFilter::TYPE;

        \Config::set('debugMode', false);

        $module = new ModuleFilter($model);

        $this->assertSame('<div class="tl_gray">    ### FILTER ###      </div>', str_replace("\n", '', trim($module->generate())));
    }

    /**
     * Tests generate() in front end mode without huh.filter.registry service.
     */
    public function testGenerateInFrontEndModeWithoutFilterRegistryService()
    {
        System::setContainer($this->container);

        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $GLOBALS['TL_LANG']['FMD'][ModuleFilter::TYPE][0] = 'Filter';

        $model = new ModuleModel();
        $model->id = 1;
        $model->type = ModuleFilter::TYPE;

        \Config::set('debugMode', false);

        $module = new ModuleFilter($model);

        $this->assertEmpty($module->generate());
    }

    /**
     * Tests generate() in front end mode without filter.
     */
    public function testGenerateInFrontEndModeWithoutFilter()
    {
        $filterConfigAdapter = $this->mockAdapter(['findByPk']);
        $filterConfigAdapter->method('findByPk')->willReturn(null);
        $session = new Session(new MockArraySessionStorage());

        $framework = $this->mockContaoFramework([FilterConfigModel::class => $filterConfigAdapter]);

        $this->container->set('huh.filter.manager', new FilterManager($framework, new FilterSession($framework, $session)));
        System::setContainer($this->container);

        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $GLOBALS['TL_LANG']['FMD'][ModuleFilter::TYPE][0] = 'Filter';

        $model = new ModuleModel();
        $model->id = 1;
        $model->type = ModuleFilter::TYPE;
        $model->filter = 1;

        \Config::set('debugMode', false);

        $module = new ModuleFilter($model);

        $this->assertEmpty($module->generate());
    }

    /**
     * Tests generate() in front end mode with filter.
     */
    public function testGenerateInFrontEndMode()
    {
        $this->container->set('kernel', $this->kernel);

        $filterConfigAdapter = $this->mockAdapter(['findByPk']);

        $filterConfigModel = $this->mockClassWithProperties(FilterConfigModel::class, ['id' => 1, 'name' => 'test', 'template' => 'bootstrap_4_layout']);
        $filterConfigModel->method('row')->willReturn(['id' => 1, 'name' => 'test', 'template' => 'bootstrap_4_layout']);

        $filterConfigAdapter->method('findByPk')->willReturn($filterConfigModel);

        $filterConfigElementAdapter = $this->mockAdapter(['findPublishedByPid']);
        $filterConfigElementAdapter->method('findPublishedByPid')->willReturn(null);

        $themeModelAdapter = $this->mockAdapter(['findAll']);
        $themeModelAdapter->method('findAll')->willReturn(null);

        $framework = $this->mockContaoFramework([ThemeModel::class => $themeModelAdapter, FilterConfigModel::class => $filterConfigAdapter, FilterConfigElementModel::class => $filterConfigElementAdapter]);

        $finder = new ResourceFinder(([
            $this->getFixturesDir(),
            __DIR__.'/../../src/Resources/contao',
            __DIR__.'/../../vendor/contao/core-bundle/src/Resources/contao',
        ]));

        $this->container->set('contao.resource_finder', $finder);

        System::setContainer($this->container);

        $this->container->setParameter('huh.filter', $this->config);
        $this->container->set('huh.utils.template', new TemplateUtil($framework));
        $this->container->set('huh.utils.container', new ContainerUtil($framework));
        $this->container->set('huh.utils.choice.twig_template', new TwigTemplateChoice($framework));

        $this->container->set('huh.utils.container', new ContainerUtil($framework));

        $templateChoiceAdapter = $this->mockAdapter(['getChoices']);
        $templateChoiceAdapter->method('getChoices')->willReturn(['bootstrap_4_layout' => '@HeimrichHannotContaoFilter/filter/filter_form_bootstrap_4_layout.html.twig']);

        $this->container->set('huh.filter.choice.template', $templateChoiceAdapter);

        $twig = $this->getMockBuilder('Twig\Environment')->disableOriginalConstructor()->getMock();
        $twig->expects($this->once())->method('render')->willReturn('test');

        $this->container->set('twig', $twig);

        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->with('filter_frontend_submit', $this->anything())->will($this->returnCallback(function ($route, $params = []) {
            return '/_filter/submit/1';
        }));

        $this->container->set('router', $router);

        /** @var Connection $connection */
        $connection = $this->container->get('database_connection');
        $session = new Session(new MockArraySessionStorage());
        $filterSession = new FilterSession($framework, $session);
        $filterQueryBuilder = new FilterQueryBuilder($framework, $connection);

        $this->container->set('huh.filter.config', new FilterConfig($framework, $filterSession, $filterQueryBuilder));
        $this->container->set('huh.filter.manager', new FilterManager($framework, $filterSession));
        System::setContainer($this->container);

        if (!defined('TL_MODE')) {
            \define('TL_MODE', 'FE');
        }

        $GLOBALS['TL_LANG']['FMD'][ModuleFilter::TYPE][0] = 'Filter';

        global $objPage;
        $objPage = new \stdClass();
        $objPage->outputFormat = '';
        $objPage->templateGroup = '';

        $model = new ModuleModel();
        $model->id = 1;
        $model->type = ModuleFilter::TYPE;
        $model->filter = 1;
        $model->cssID = [0 => 'cssId', '1' => 'cssClass'];

        \Config::set('debugMode', false);

        $module = new ModuleFilter($model);
        $result = $module->generate();

        $this->assertNotEmpty($result);
        $this->assertSame('<!-- indexer::stop --><div class="mod_filter cssClass block" id="cssId">              test</div><!-- indexer::continue -->', str_replace("\n", '', trim($result)));
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures';
    }

    /**
     * Mocks the plugin loader.
     *
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expects
     * @param array                                              $plugins
     *
     * @return PluginLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPluginLoader(\PHPUnit_Framework_MockObject_Matcher_InvokedCount $expects, array $plugins = [])
    {
        $pluginLoader = $this->createMock(PluginLoader::class);

        $pluginLoader->expects($expects)->method('getInstancesOf')->with(PluginLoader::EXTENSION_PLUGINS)->willReturn($plugins);

        return $pluginLoader;
    }
}
