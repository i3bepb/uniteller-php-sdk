<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\Fiscal;
use Tmconsulting\Uniteller\Receipt\FiscalReceipt;
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\PaymentInfo;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\FiscalReceipt
 */
class FiscalReceiptTest extends TestCase
{
    public function testJsonSerializeWithFiscalAndUserRequisite()
    {
        $item = $this->createMock(Item::class);
        $item->method('jsonSerialize')->willReturn(['name' => 'Товар', 'price' => 100]);

        $payment = $this->createMock(PaymentInfo::class);
        $payment->method('jsonSerialize')->willReturn(['kind' => 1, 'type' => 1, 'amount' => 100]);

        $fiscal = $this->createMock(Fiscal::class);
        $fiscal->method('jsonSerialize')->willReturn(['fn' => '123456789']);

        $userRequisite = ['ИНН' => '1234567890', 'КПП' => '987654321'];

        $receipt = new FiscalReceipt(
            $fiscal,
            1,
            [$item],
            [$payment],
            100.0,
            null,
            null,
            null,
            null,
            null,
            $userRequisite
        );

        $data = $receipt->jsonSerialize();

        $this->assertArrayHasKey('fiscal', $data);
        $this->assertSame($fiscal, $data['fiscal']);

        $this->assertArrayHasKey('userrequisite', $data);
        $this->assertJson($data['userrequisite']);
        $decoded = json_decode($data['userrequisite'], true);
        $this->assertEquals('1234567890', $decoded['ИНН']);
    }

    public function testJsonSerializeWithoutUserRequisite()
    {
        $item = $this->createMock(Item::class);
        $payment = $this->createMock(PaymentInfo::class);
        $fiscal = $this->createMock(Fiscal::class);
        $fiscal->method('jsonSerialize')->willReturn(['fn' => '987']);

        $receipt = new FiscalReceipt(
            $fiscal,
            2,
            [$item],
            [$payment],
            150.5
        );

        $data = $receipt->jsonSerialize();

        $this->assertArrayHasKey('fiscal', $data);
        $this->assertSame($fiscal, $data['fiscal']);
        $this->assertArrayNotHasKey('userrequisite', $data);
    }

    public function testToJsonAndBase64()
    {
        $item = $this->createMock(Item::class);
        $item->method('jsonSerialize')->willReturn(['name' => 'Test item']);

        $payment = $this->createMock(PaymentInfo::class);
        $payment->method('jsonSerialize')->willReturn(['kind' => 1, 'type' => 2, 'amount' => 50]);

        $fiscal = $this->createMock(Fiscal::class);
        $fiscal->method('jsonSerialize')->willReturn(['fn' => 'test']);

        $receipt = new FiscalReceipt(
            $fiscal,
            1,
            [$item],
            [$payment],
            50
        );

        $json = $receipt->toJson();
        $this->assertJson($json);
        $this->assertStringContainsString('"taxmode":1', $json);
        $this->assertStringContainsString('"total":50', $json);
        $this->assertStringContainsString('"fiscal"', $json);

        $base64 = $receipt->toBase64();
        $this->assertNotEmpty($base64);
        $this->assertEquals(
            base64_decode($base64),
            $json
        );
    }
}
