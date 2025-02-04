<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Fiscal;

use Tmconsulting\Uniteller\Receipt\Fiscal\Register;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Fiscal\Register
 */
class RegisterTest extends TestCase
{
    public function testJsonSerializeWithAllFields()
    {
        $register = new Register(
            '1234567890',
            '15',
            '001',
            '2024-01-01T12:00:00',
            '987654321',
            '2024-01-01T12:05:00',
            '123abc456',
            'https://nalog.ru/check',
            'qr-code-data',
            ['mark' => 'abc', 'code' => 123]
        );

        $expected = [
            'fiscal_number' => '1234567890',
            'shift_number'  => '15',
            'shift_index'   => '001',
            'fiscal_date'   => '2024-01-01T12:00:00',
            'fiscal_attr'   => '987654321',
            'fdo_date'      => '2024-01-01T12:05:00',
            'fdo_attr'      => '123abc456',
            'fiscal_link'   => 'https://nalog.ru/check',
            'qr'            => 'qr-code-data',
            'markinginfo'   => json_encode(['mark' => 'abc', 'code' => 123]),
        ];

        $this->assertSame($expected, $register->jsonSerialize());
    }

    public function testJsonEncode()
    {
        $register = new Register(
            '111222333',
            '1',
            '2',
            '2023-05-05T10:10:10',
            '999',
            '2023-05-05T10:15:00',
            'attr987'
        );

        $expectedJson = json_encode([
            'fiscal_number' => '111222333',
            'shift_number'  => '1',
            'shift_index'   => '2',
            'fiscal_date'   => '2023-05-05T10:10:10',
            'fiscal_attr'   => '999',
            'fdo_date'      => '2023-05-05T10:15:00',
            'fdo_attr'      => 'attr987',
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($register));
    }
}
