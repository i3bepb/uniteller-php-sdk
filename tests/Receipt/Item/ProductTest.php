<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Item;

use Tmconsulting\Uniteller\Receipt\Item\Product;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Item\Product
 */
class ProductTest extends TestCase
{
    public function testJsonSerialize()
    {
        $product = new Product('12345', '10.00', '643', '10702000/123456/0012345');

        $expected = [
            'kt'  => '12345',
            'exc' => '10.00',
            'coc' => '643',
            'ncd' => '10702000/123456/0012345',
        ];

        $this->assertSame($expected, $product->jsonSerialize());
    }

    public function testJsonSerializeWithHexKt()
    {
        $ktHex = '0x444D02A3357919913962546563775844562e705a46';
        $product = new Product($ktHex, '0.00', '156', '12345678');

        $expected = [
            'kt'  => $ktHex,
            'exc' => '0.00',
            'coc' => '156',
            'ncd' => '12345678',
        ];

        $this->assertSame($expected, $product->jsonSerialize());
    }

    public function testJsonEncodeMatchesExpectedJson()
    {
        $product = new Product('ABCD', '5.50', '840', 'TNVD-998877');

        $expectedJson = json_encode([
            'kt'  => 'ABCD',
            'exc' => '5.50',
            'coc' => '840',
            'ncd' => 'TNVD-998877',
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($product));
    }
}
