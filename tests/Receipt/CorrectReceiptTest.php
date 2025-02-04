<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\CorrectReceipt;
use Tmconsulting\Uniteller\Receipt\Enum\CorrectionType;
use Tmconsulting\Uniteller\Receipt\Enum\Taxmode;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\CorrectReceipt
 */
class CorrectReceiptTest extends TestCase
{
    public function testJsonSerialize()
    {
        $date = '2025-04-14';
        $correctReceipt = new CorrectReceipt(
            CorrectionType::DIRECTIVE,
            new \DateTime($date),
            '1234567890',
            Taxmode::SIMPLIFIED_INCOME,
            [], // Платежи (оставим пустым для теста)
            100.50,
            20.10,
            10.05,
            0.00,
            80.00,
            16.70,
            9.10
        );

        $expected = [
            'correctionType'      => CorrectionType::DIRECTIVE,
            'causeDocumentDate'   => $date,
            'causeDocumentNumber' => '1234567890',
            'taxmode'             => Taxmode::SIMPLIFIED_INCOME,
            'payments'            => [],
            'total'               => 100.50,
            'tax1Sum'             => 20.10,
            'tax2Sum'             => 10.05,
            'tax3Sum'             => 0.00,
            'tax4Sum'             => 80.00,
            'tax5Sum'             => 16.70,
            'tax6Sum'             => 9.10,
        ];

        $this->assertSame($expected, $correctReceipt->jsonSerialize());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($correctReceipt));
    }
}
