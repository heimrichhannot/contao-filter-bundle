<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Test\FilterQuery;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPart;
use HeimrichHannot\FilterBundle\FilterQuery\FilterQueryPartCollection;

class FilterQueryPartCollectionTest extends ContaoTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function createTestInstance($mockBuilder = false)
    {
        if ($mockBuilder) {
            $instance = $this->getMockBuilder(FilterQueryPartCollection::class)
                ->getMock();
        } else {
            $instance = new FilterQueryPartCollection();
        }

        return $instance;
    }

    public function mockQueryPart(string $name = 'teaser_70', string $field = 'teaser'): FilterQueryPart
    {
        $part = $this->createMock(FilterQueryPart::class);
        $part->method('getField')->willReturn($field);
        $part->method('isInitial')->willReturn(false);
        $part->method('isOverridable')->willReturn(false);
        $part->method('getName')->willReturn($name);

        return $part;
    }

    public function testGetParts()
    {
        $instance = $this->createTestInstance();

        $this->assertCount(0, $instance->getParts());
        $this->assertInternalType('array', $instance->getParts());
        $this->assertEmpty($instance->getParts());

        $instance->addPart($this->mockQueryPart());

        $this->assertCount(1, $instance->getParts());
        $this->assertInternalType('array', $instance->getParts());
        $this->assertNotEmpty($instance->getParts());
    }

    public function testGetPartByName()
    {
        $instance = $this->createTestInstance();
        $part = $this->mockQueryPart();

        $instance->addPart($part);

        $this->assertSame($part, $instance->getPartByName('teaser_70'));
    }

    public function testAddPart()
    {
        $instance = $this->createTestInstance();
        $part = $this->mockQueryPart();

        $this->assertEmpty($instance->getParts());
        $this->assertEmpty($instance->getTargetFields());

        $instance->addPart($part);

        $this->assertNotEmpty($instance->getParts());
        $this->assertSame($part, $instance->getParts()['teaser_70']);
        $this->assertNotEmpty($instance->getTargetFields());
    }

    public function testRemovePartByName()
    {
        $instance = $this->createTestInstance();
        $instance->addPart($part = $this->mockQueryPart('teaser_71'));

        $this->assertCount(1, $instance->getParts());
        $this->assertSame($part, $instance->getPartByName('teaser_71'));

        $instance->removePartByName('teaser_71');

        $this->assertCount(0, $instance->getParts());
        $this->assertNull($instance->getPartByName('teaser_71'));
    }

    public function testAddTargetField()
    {
        $instance = $this->createTestInstance();

        $this->assertCount(0, $instance->getTargetFields());

        $instance->addTargetField('teaser', 'teaser_71', false, false);
        $this->assertCount(1, $instance->getTargetFields());
        $this->assertCount(1, $instance->getTargetFields()['teaser']);
        $this->assertFalse($instance->getTargetFields()['teaser']['teaser_71']['initial']);
        $this->assertFalse($instance->getTargetFields()['teaser']['teaser_71']['overridable']);

        $instance->addTargetField('teaser', 'teaser_72', true, false);
        $this->assertCount(1, $instance->getTargetFields());
        $this->assertCount(2, $instance->getTargetFields()['teaser']);
        $this->assertTrue($instance->getTargetFields()['teaser']['teaser_72']['initial']);
        $this->assertFalse($instance->getTargetFields()['teaser']['teaser_72']['overridable']);

        $instance->addTargetField('content', 'content_73', false, true);
        $this->assertCount(2, $instance->getTargetFields());
        $this->assertCount(2, $instance->getTargetFields()['teaser']);
        $this->assertCount(1, $instance->getTargetFields()['content']);
        $this->assertFalse($instance->getTargetFields()['content']['content_73']['initial']);
        $this->assertTrue($instance->getTargetFields()['content']['content_73']['overridable']);
    }

    public function testGetTargetFields()
    {
        $instance = $this->createTestInstance();

        $instance->addPart($this->mockQueryPart());

        $this->assertInternalType('array', $instance->getTargetFields());
        $this->assertCount(1, $instance->getTargetFields());
        $this->assertArrayHasKey('teaser', $instance->getTargetFields());
        $this->assertCount(1, $instance->getTargetFields()['teaser']);
        $this->assertArrayHasKey('teaser_70', $instance->getTargetFields()['teaser']);
    }

    public function testRemoveTargetField()
    {
        $instance = $this->createTestInstance();

        $instance->addTargetField('teaser', 'teaser_71', false, false);
        $instance->addTargetField('teaser', 'teaser_72', true, false);
        $instance->addTargetField('content', 'content_73', false, true);

        $this->assertArrayHasKey('teaser', $instance->getTargetFields());
        $this->assertArrayHasKey('teaser_71', $instance->getTargetFields()['teaser']);
        $this->assertArrayHasKey('teaser_72', $instance->getTargetFields()['teaser']);
        $this->assertArrayHasKey('content', $instance->getTargetFields());
        $this->assertArrayHasKey('content_73', $instance->getTargetFields()['content']);

        $this->assertCount(2, $instance->getTargetFields());
        $this->assertCount(2, $instance->getTargetFields()['teaser']);
        $this->assertCount(1, $instance->getTargetFields()['content']);

        $instance->removeTargetField();
        $this->assertCount(2, $instance->getTargetFields());
        $this->assertCount(2, $instance->getTargetFields()['teaser']);
        $this->assertCount(1, $instance->getTargetFields()['content']);

        $instance->removeTargetField('', 'teaser_71');
        $this->assertCount(2, $instance->getTargetFields());
        $this->assertCount(2, $instance->getTargetFields()['teaser']);
        $this->assertCount(1, $instance->getTargetFields()['content']);

        $instance->removeTargetField('content', 'teaser_71');
        $this->assertCount(2, $instance->getTargetFields());
        $this->assertCount(2, $instance->getTargetFields()['teaser']);
        $this->assertCount(1, $instance->getTargetFields()['content']);

        $instance->removeTargetField('teaser', 'teaser_71');
        $this->assertCount(2, $instance->getTargetFields());
        $this->assertCount(1, $instance->getTargetFields()['teaser']);
        $this->assertCount(1, $instance->getTargetFields()['content']);

        $instance->removeTargetField('content', 'content_73');
        $this->assertCount(1, $instance->getTargetFields());
        $this->assertCount(1, $instance->getTargetFields()['teaser']);
        $this->assertArrayNotHasKey('content', $instance->getTargetFields());
    }

    public function testReset()
    {
        $instance = $this->createTestInstance();

        $instance->addPart($this->mockQueryPart());
        $this->assertCount(1, $instance->getParts());
        $this->assertCount(1, $instance->getTargetFields());

        $instance->reset();
        $this->assertEmpty($instance->getParts());
        $this->assertEmpty($instance->getTargetFields());
    }
}
