<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\Model;


use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use Symfony\Component\HttpKernel\Kernel;

class FilterConfigModelTest extends ContaoTestCase
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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $GLOBALS['TL_LANGUAGE']    = 'en';
        $GLOBALS['TL_LANG']['MSC'] = ['test' => 'bar'];

        $GLOBALS['TL_DCA']['tl_filter_config'] = [
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

        $this->container = $this->mockContainer();
        $this->container->setParameter('kernel.debug', true);
        $this->container->setParameter('kernel.default_locale', 'de');
        $this->container->setParameter('kernel.cache_dir', $this->getFixturesDir());

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
    }

    /**
     * Test findPublishedByPid()
     */
    public function testFindAllPublished()
    {
        $modelA = $this->mockClassWithProperties(FilterConfigModel::class, [
            'id'  => 1,
            'pid' => 1
        ]);

        $modelAdapter = $this->mockAdapter(['findBy']);
        $modelAdapter->method('findBy')->willReturn([$modelA]);

        $framework = $this->mockContaoFramework([Model::class => $modelAdapter]);

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigModel = new FilterConfigModel();
        $result            = $filterConfigModel->findAllPublished();

        $this->assertNotNull($result);
        $this->assertEquals($modelA, $result[0]);
    }

    /**
     * Test findPublishedByPid() without Contao\Model Adapter
     */
    public function testFindAllPublishedWithoutAdapter()
    {
        $framework = $this->mockContaoFramework();

        $this->container->set('contao.framework', $framework);

        System::setContainer($this->container);

        $filterConfigModel = new FilterConfigModel();
        $result            = $filterConfigModel->findAllPublished();

        $this->assertNull($result);
    }

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures';
    }
}