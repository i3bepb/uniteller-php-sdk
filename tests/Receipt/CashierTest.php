<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\Cashier;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Cashier
 */
class CashierTest extends TestCase
{
    public function testJsonSerializeWithNameAndInn()
    {
        $cashier = new Cashier('Иванов Иван Иванович', 1234567890);
        $expected = [
            'name' => 'Иванов Иван Иванович',
            'inn' => 1234567890,
        ];
        $this->assertSame($expected, $cashier->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($cashier));
    }

    public function testJsonSerializeWithOnlyName()
    {
        $cashier = new Cashier('Иванов Иван Иванович');
        $expected = [
            'name' => 'Иванов Иван Иванович',
            'inn' => 0, // Инн будет 0, так как не был передан
        ];
        $this->assertSame($expected, $cashier->jsonSerialize());
    }

    public function testJsonSerializeWithOnlyInn()
    {
        $cashier = new Cashier(null, 1234567890);
        $expected = [
            'name' => null,
            'inn' => 1234567890,
        ];
        $this->assertSame($expected, $cashier->jsonSerialize());
    }

    public function testJsonSerializeWithNullValues()
    {
        $cashier = new Cashier();
        $expected = [
            'name' => null,
            'inn' => 0, // Инн будет 0, так как не был передан
        ];
        $this->assertSame($expected, $cashier->jsonSerialize());
    }
}
