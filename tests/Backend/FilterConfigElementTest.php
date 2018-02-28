<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Tests\Backend;


use Contao\DataContainer;
use Contao\Model;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\Backend\FilterConfigElement;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class FilterConfigElementTest extends ContaoTestCase
{
    /**
     * Tests modifyPalette() without existing tl_filter_config_element model
     */
    public function testModifyPaletteWithoutFilterConfigElementModel()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class                    => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        System::setContainer($container);

        $instance = new FilterConfigElement();

        $this->assertNull($instance->modifyPalette($this->getDataContainerMock()));
    }

    /**
     * Tests modifyPalette() without any existing huh.filter types
     */
    public function testModifyPaletteWithoutFilterTypes()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1];
        $filterConfigElementModel      = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class                    => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', ['filter' => [[]]]);

        System::setContainer($container);

        $instance = new FilterConfigElement();

        $this->assertNull($instance->modifyPalette($this->getDataContainerMock()));
    }

    /**
     * Tests modifyPalette() without found huh.filter type
     */
    public function testModifyPaletteWithoutFoundFilterType()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'text'];
        $filterConfigElementModel      = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $framework = $this->mockContaoFramework(
            [
                Model::class                    => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', ['filter' => ['types' => [['name' => 'choice']]]]);

        System::setContainer($container);

        $instance = new FilterConfigElement();

        $this->assertNull($instance->modifyPalette($this->getDataContainerMock()));
    }


    /**
     * Tests modifyPalette() without any existing huh.filter types
     */
    public function testModifyInitialPalette()
    {
        $container = $this->mockContainer();

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->willReturn(FilterConfigElementModel::class);

        $filterConfigElementProperties = ['id' => 1, 'pid' => 1, 'type' => 'text', 'isInitial' => true];
        $filterConfigElementModel      = $this->mockClassWithProperties(FilterConfigElementModel::class, $filterConfigElementProperties);

        $filterConfigElementModelAdapter = $this->mockAdapter(['findByPk', 'findPublishedByPid']);
        $filterConfigElementModelAdapter->method('findByPk')->willReturn($filterConfigElementModel);
        $filterConfigElementModelAdapter->method('findPublishedByPid')->willReturn(null);

        $GLOBALS['TL_DCA']['tl_filter_config_element'] = [
            'palettes' => [
                'text' => '{general_legend},title,type,isInitial;{config_legend},field,customName,customOperator,addDefaultValue;{visualization_legend},addPlaceholder,customLabel,hideLabel,inputGroup;{expert_legend},cssClass;{publish_legend},published;'
            ]
        ];

        $framework = $this->mockContaoFramework(
            [
                Model::class                    => $modelAdapter,
                FilterConfigElementModel::class => $filterConfigElementModelAdapter,
            ]
        );

        $container->set('contao.framework', $framework);

        $modelsUtil = new ModelUtil($framework);
        $container->set('huh.utils.model', $modelsUtil);

        $container->setParameter('huh.filter', [
            'filter' => [
                'types' => [
                    [
                        'name'  => 'text',
                        'class' => 'HeimrichHannot\FilterBundle\Filter\Type\TextType',
                        'type'  => 'text'
                    ]
                ]
            ]
        ]);

        System::setContainer($container);

        $instance = new FilterConfigElement();
        $instance->modifyPalette($this->getDataContainerMock());

        $this->assertEquals(FilterConfigElement::INITIAL_PALETTE, $GLOBALS['TL_DCA']['tl_filter_config_element']['palettes']['text']);
    }

    /**
     * @return DataContainer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDataContainerMock(array $properties = [])
    {
        if (empty($properties)) {
            $properties = ['id' => 1, 'table' => 'tl_filter_config_element'];
        }

        return $this->mockClassWithProperties(DataContainer::class, $properties);
    }
}