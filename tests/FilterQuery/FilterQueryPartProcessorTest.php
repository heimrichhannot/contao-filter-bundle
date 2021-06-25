<?php
/**
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\FilterQuery;

use Contao\TestCase\ContaoTestCase;
use Doctrine\DBAL\Connection;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPart;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartProcessor;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\FilterBundle\Type\FilterTypeContext;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Date\DateUtil;

class FilterQueryPartProcessorTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function createTestInstance(array $parameters = [], $mockBuilder = false)
    {
        $connection = $parameters['connection'] ?? $this->createMock(Connection::class);
        $dateUtil = $parameters['dateUtil'] ?? $this->createMock(DateUtil::class);
        $databaseUtil = $parameters['databaseUtil'] ?? $this->createMock(DatabaseUtil::class);

        if ($mockBuilder) {
            $instance = $this->getMockBuilder(FilterQueryPartProcessor::class)
                ->setConstructorArgs([$connection, $dateUtil, $databaseUtil])
                ->getMock();
        } else {
            $instance = new FilterQueryPartProcessor($connection, $dateUtil, $databaseUtil);
        }

        return $instance;
    }

    public function testComposeQueryPart()
    {
        $instance = $this->createTestInstance();
        $context = $this->createMock(FilterTypeContext::class);
        $filterConfigModel = $this->createMock(FilterConfigModel::class);
        $filterConfigModel->method('row')->willReturn(['dataContainer' => 'tl_news']);
        $context->method('getFilterConfig')->willReturn($filterConfigModel);

        $this->assertInstanceOf(FilterQueryPart::class, $instance->composeQueryPart($context));
    }
}