<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Fiscal;

use Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator
 */
class FiscalDataOperatorTest extends TestCase
{
    public function testJsonSerializeReturnsExpectedArray()
    {
        $operator = new FiscalDataOperator('ООО "Контур ОФД"', '7701234567', 'https://ofd.kontur.ru');

        $expected = [
            'name' => 'ООО "Контур ОФД"',
            'inn'  => '7701234567',
            'www'  => 'https://ofd.kontur.ru',
        ];

        $this->assertSame($expected, $operator->jsonSerialize());
    }

    public function testJsonEncodeMatchesExpectedJson()
    {
        $operator = new FiscalDataOperator('ООО "Такском"', '7704567890', 'https://ofd.taxcom.ru');

        $expectedJson = '{"name":"ООО \"Такском\"","inn":"7704567890","www":"https://ofd.taxcom.ru"}';
        $actualJson = json_encode($operator);

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }
}
