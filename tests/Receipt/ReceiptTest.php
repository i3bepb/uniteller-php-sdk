<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\Cashier;
use Tmconsulting\Uniteller\Receipt\Customer;
use Tmconsulting\Uniteller\Receipt\Enum\Taxmode;
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\Params;
use Tmconsulting\Uniteller\Receipt\PaymentInfo;
use Tmconsulting\Uniteller\Receipt\Receipt;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Receipt
 */
class ReceiptTest extends TestCase
{
    public function testToJson()
    {
        $item = $this->createMock(Item::class);
        $item->method('jsonSerialize')->willReturn(['name' => 'name1']);
        $payment = $this->createMock(PaymentInfo::class);
        $payment->method('jsonSerialize')->willReturn(['kind' => 2]);

        $receipt = new Receipt(
            Taxmode::SIMPLIFIED_INCOME,
            [$item],
            [$payment],
            100.00
        );

        $expected = [
            'taxmode'  => 1,
            'lines'    => [['name' => 'name1']],
            'payments' => [['kind' => 2]],
            'total'    => 100.0,
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $receipt->toJson());
    }

    public function testJsonSerialize()
    {
        $item = $this->createMock(Item::class);
        $payment = $this->createMock(PaymentInfo::class);
        $customer = $this->createMock(Customer::class);
        $cashier = $this->createMock(Cashier::class);
        $params = new Params('https://mysite.test');
        $optional = ['extra' => 'value'];

        $receipt = new Receipt(
            2,
            [$item],
            [$payment],
            200.50,
            'email@example.com',
            $optional,
            $customer,
            $cashier,
            $params
        );

        $data = $receipt->jsonSerialize();

        $this->assertSame(2, $data['taxmode']);
        $this->assertSame([$item], $data['lines']);
        $this->assertSame([$payment], $data['payments']);
        $this->assertSame(200.5, $data['total']);
        $this->assertSame($optional, $data['optional']);
        $this->assertSame($customer, $data['customer']);
        $this->assertSame($cashier, $data['cashier']);
        $this->assertSame($params, $data['params']);
    }

    public function testToBase64()
    {
        $item = $this->createMock(Item::class);
        $item->method('jsonSerialize')->willReturn(['name' => 'Base64', 'price' => 10]);

        $payment = $this->createMock(PaymentInfo::class);
        $payment->method('jsonSerialize')->willReturn(['kind' => 2, 'type' => 1, 'amount' => 10]);

        $receipt = new Receipt(
            4,
            [$item],
            [$payment],
            10.0
        );

        $base64 = $receipt->toBase64();

        $this->assertNotEmpty($base64);
        $this->assertEquals(
            base64_decode($base64),
            $receipt->toJson()
        );
    }
}
