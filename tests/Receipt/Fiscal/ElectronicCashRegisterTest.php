<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Fiscal;

use Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister
 */
class ElectronicCashRegisterTest extends TestCase
{
    public function testJsonSerializeReturnsExpectedArray()
    {
        $cashRegister = new ElectronicCashRegister('SN123456', 'RN987654', 'FS555888');

        $expected = [
            'sn' => 'SN123456',
            'rn' => 'RN987654',
            'fs' => 'FS555888',
        ];

        $this->assertSame($expected, $cashRegister->jsonSerialize());
    }

    public function testJsonEncodeMatchesExpectedJson()
    {
        $cashRegister = new ElectronicCashRegister('SN000001', 'RN000002', 'FS000003');

        $expectedJson = '{"sn":"SN000001","rn":"RN000002","fs":"FS000003"}';
        $actualJson = json_encode($cashRegister);

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }
}
