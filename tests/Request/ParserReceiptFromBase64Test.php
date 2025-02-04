<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Tmconsulting\Uniteller\Receipt\Enum\AgentAttribute;
use Tmconsulting\Uniteller\Receipt\FiscalReceipt;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
 */
class ParserReceiptFromBase64Test extends TestCase
{
    public function testParse()
    {
        $arr = [
            [
                'customer'      => [
                    'email' => 'test@gmail.com',
                ],
                'taxmode'       => 1,
                'lines'         => [
                    [
                        'name'     => 'Услуга №3',
                        'price'    => 20,
                        'qty'      => 1,
                        'sum'      => 20,
                        'vat'      => 5,
                        'payattr'  => 4,
                        'lineattr' => 4,
                        'unit'     => 0,
                        'agent'    => [
                            'agentattr'     => AgentAttribute::AGENT,
                            'agentphone'    => '+78002000600',
                            'suppliername'  => 'ООО «Рога и Копыта»',
                            'supplierinn'   => 1234567890,
                            'supplierphone' => '+78002000602',
                        ],
                    ]
                ],
                'payments'      => [
                    [
                        'kind'   => 1,
                        'type'   => 0,
                        'amount' => 20,
                    ]
                ],
                'fiscal'        => [
                    'id'       => '1-1024785753',
                    'date'     => '2025-03-03 21:03:28',
                    'type'     => 1,
                    'register' => [
                        'fiscal_number' => '1741025009',
                        'shift_number'  => '3',
                        'shift_index'  => '12',
                        'fiscal_date'   => '2025-03-03 21:03:28',
                        'fiscal_attr'   => '1298498081',
                        'fdo_date'      => '2025-03-03 21:03:28',
                        'fdo_attr'      => '562193741',
                        'fiscal_link'   => 'www.nalog.ru',
                        'qr'            => 't=20170608T1846\u0026s=2400.00\u0026fn=9999991234567890\u0026i=27\u0026fp=1298498081\u0026n=1',
                        'markinginfo'   => [null],
                    ],
                    'ecr'      => [
                        'sn' => '10000000000000000152',
                        'rn' => '2505480089016782',
                        'fs' => '9999991234567890',
                    ],
                    'company'  => [
                        'name' => 'ИП Иванов И.И.',
                        'inn'  => '1234567890',
                    ],
                    'fdo'      => [
                        'name' => '',
                        'inn'  => '',
                        'www'  => '',
                    ],
                ],
                'total'         => 20,
                'userrequisite' => [
                    'title' => 'UnitellerOrderNumber',
                    'value' => 'receipt_order_3',
                ],
            ]
        ];
        $receiptBase64 = base64_encode(json_encode($arr));
        $parser = new ParserReceiptFromBase64();
        $receipts = $parser->parse($receiptBase64);
        $this->assertIsArray($receipts);
        $this->assertCount(1, $receipts);
        foreach ($receipts as $receipt) {
            $this->assertInstanceOf(FiscalReceipt::class, $receipt);
        }
    }

    public function testParseEmptyBase64()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty base64 receipt');
        $parser = new ParserReceiptFromBase64();
        $parser->parse('');
    }

    public function testParseInvalidBase64()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not valid base64 receipt');
        $parser = new ParserReceiptFromBase64();
        $parser->parse('not_a_base64_string');
    }

    public function testParseInvalidJson()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not valid json receipt');
        $invalidJson = base64_encode('{"invalid": json');
        $parser = new ParserReceiptFromBase64();
        $parser->parse($invalidJson);
    }

    public function testParseMultipleReceipts()
    {
        $data = [
            [
                'fiscal'   => [
                    'id'       => '1',
                    'date'     => '2023-01-01',
                    'type'     => 1,
                    'ecr'      => ['sn' => 'SN1', 'rn' => 'RN1', 'fs' => 'FS1'],
                    'company'  => ['name' => 'Company 1', 'inn' => 1111111111],
                    'fdo'      => ['name' => 'FDO 1', 'inn' => 111111111, 'www' => 'fdo1.test'],
                    'register' => [
                        'fiscal_number' => 'FN1',
                        'shift_number'  => 1,
                        'shift_index'   => 1,
                        'fiscal_date'   => '2023-01-01',
                        'fiscal_attr'   => 'attr1',
                        'fdo_date'      => '2023-01-01',
                        'fdo_attr'      => 'fdo_attr1'
                    ]
                ],
                'taxmode'  => 1,
                'lines'    => [],
                'payments' => []
            ],
            [
                'fiscal'   => [
                    'id'       => '2',
                    'date'     => '2023-01-02',
                    'type'     => 1,
                    'ecr'      => ['sn' => 'SN2', 'rn' => 'RN2', 'fs' => 'FS2'],
                    'company'  => ['name' => 'Company 2', 'inn' => 2222222222],
                    'fdo'      => ['name' => 'FDO 2', 'inn' => 222222222, 'www' => 'fdo2.test'],
                    'register' => [
                        'fiscal_number' => 'FN2',
                        'shift_number'  => 2,
                        'shift_index'   => 2,
                        'fiscal_date'   => '2023-01-02',
                        'fiscal_attr'   => 'attr2',
                        'fdo_date'      => '2023-01-02',
                        'fdo_attr'      => 'fdo_attr2'
                    ]
                ],
                'taxmode'  => 2,
                'lines'    => [],
                'payments' => []
            ]
        ];

        $base64 = base64_encode(json_encode($data));
        $parser = new ParserReceiptFromBase64();
        $receipts = $parser->parse($base64);

        $this->assertCount(2, $receipts);
        $this->assertInstanceOf(FiscalReceipt::class, $receipts[0]);
        $this->assertInstanceOf(FiscalReceipt::class, $receipts[1]);
    }
}
