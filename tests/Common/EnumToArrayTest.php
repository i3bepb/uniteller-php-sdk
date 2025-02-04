<?php

namespace Tmconsulting\Uniteller\Tests\Common;

use Tmconsulting\Uniteller\Common\EnumToArray;
use Tmconsulting\Uniteller\Tests\TestCase;

class EnumToArrayTest extends TestCase
{
    const A = 9;

    const B = 'b',
        C = 'common';

    use EnumToArray;

    /**
     * @test
     *
     * @covers \Tmconsulting\Uniteller\Common\EnumToArray
     */
    public function testToArray()
    {
        $this->assertIsArray($this->toArray());
        $this->assertCount(3, $this->toArray());
        $this->assertEquals(9, $this->toArray()['A']);
        $this->assertEquals('b', $this->toArray()['B']);
        $this->assertEquals('common', $this->toArray()['C']);
    }

    /**
     * When not set const, but use EnumToArray
     *
     * @test
     *
     * @covers \Tmconsulting\Uniteller\Common\EnumToArray
     */
    public function testToArrayWithoutConst()
    {
        $objectWithTrait = $this->getObjectForTrait(EnumToArray::class);
        $this->assertIsArray($objectWithTrait->toArray());
        $this->assertCount(0, $objectWithTrait->toArray());
    }
}
