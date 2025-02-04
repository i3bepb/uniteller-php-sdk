<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Item;

use Tmconsulting\Uniteller\Receipt\Item\QtyPart;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Item\QtyPart
 */
class QtyPartTest extends TestCase
{
    public static function dataProviderJsonSerialize(): array
    {
        return [
            [5, 8],
            [10, 50],
        ];
    }

    /**
     * @dataProvider dataProviderJsonSerialize
     */
    public function testJsonSerialize($numerator, $denominator)
    {
        $qtyPart = new QtyPart($numerator, $denominator);

        $expected = [
            'numerator' => $numerator,
            'denominator' => $denominator,
        ];

        $this->assertSame($expected, $qtyPart->jsonSerialize());
    }

    /**
     * @dataProvider dataProviderJsonSerialize
     */
    public function testJsonEncodeMatchesExpectedJson($numerator, $denominator)
    {
        $qtyPart = new QtyPart($numerator, $denominator);

        $expectedJson = json_encode([
            'numerator' => $numerator,
            'denominator' => $denominator,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($qtyPart));
    }
}
