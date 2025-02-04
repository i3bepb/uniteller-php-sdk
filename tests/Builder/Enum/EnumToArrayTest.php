<?php

namespace Tmconsulting\Uniteller\Tests\Builder\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait
 */
class EnumToArrayTest extends TestCase
{
    const A = 9;

    const B = 'b',
        C = 'common';

    use EnumToArrayTrait;

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
     */
    public function testToArrayWithoutConst()
    {
        $objectWithTrait = $this->getObjectForTrait(EnumToArrayTrait::class);
        $this->assertIsArray($objectWithTrait->toArray());
        $this->assertCount(0, $objectWithTrait->toArray());
    }
}
