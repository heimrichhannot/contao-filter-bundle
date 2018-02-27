<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\Utils;

use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Util\FilterConfigElementHelper;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class FilterConfigElementHelperTest extends ContaoTestCase
{
    public function setUp()
    {
        $container  = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn('');

        $framework = $this->mockContaoFramework([Model::class => $modelAdapter]);

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);
        System::setContainer($container);

        if (!\function_exists('standardize')) {
            include_once __DIR__ . '/../../vendor/contao/core-bundle/src/Resources/contao/helper/functions.php';
        }
    }

    /**
     * Tests getFields() if no models exists
     */
    public function testGetFieldsWithoutModels()
    {
        $this->assertEmpty(FilterConfigElementHelper::getFields($this->getDataContainerMock()));
    }

    /**
     * @return DataContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDataContainerMock(array $properties = [])
    {
        if (empty($properties)) {
            $properties = ['id' => 1, 'table' => 'testTable'];
        }

        return $this->mockClassWithProperties(DataContainer::class, $properties);
    }
}