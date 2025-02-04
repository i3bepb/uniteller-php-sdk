<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\Enum\DocumentType;
use Tmconsulting\Uniteller\Receipt\Fiscal;
use Tmconsulting\Uniteller\Receipt\Fiscal\Company;
use Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister;
use Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator;
use Tmconsulting\Uniteller\Receipt\Fiscal\Register;
use Tmconsulting\Uniteller\Receipt\Params;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Fiscal
 */
class FiscalTest extends TestCase
{
    public function testJsonSerialize()
    {
        $id = 'abc123';
        $date = '2024-04-14 12:00:00';

        $ecr = $this->createMock(ElectronicCashRegister::class);
        $company = $this->createMock(Company::class);
        $fdo = $this->createMock(FiscalDataOperator::class);
        $register = $this->createMock(Register::class);
        $params = $this->createMock(Params::class);

        $ecr->method('jsonSerialize')->willReturn(['ecr' => 'data']);
        $company->method('jsonSerialize')->willReturn(['company' => 'info']);
        $fdo->method('jsonSerialize')->willReturn(['fdo' => 'info']);
        $register->method('jsonSerialize')->willReturn(['reg' => 'data']);
        $params->method('jsonSerialize')->willReturn(['place' => 'https://example.com']);

        $optional = ['customKey' => 'customValue'];

        $fiscal = new Fiscal($id, $date, DocumentType::INCOME, $ecr, $company, $fdo, $register, $optional, $params);

        $expected = [
            'id'       => $id,
            'date'     => $date,
            'type'     => DocumentType::INCOME,
            'register' => ['reg' => 'data'],
            'ecr'      => ['ecr' => 'data'],
            'company'  => ['company' => 'info'],
            'fdo'      => ['fdo' => 'info'],
            'optional' => $optional,
            'params'   => ['place' => 'https://example.com'],
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($fiscal));
    }
}
