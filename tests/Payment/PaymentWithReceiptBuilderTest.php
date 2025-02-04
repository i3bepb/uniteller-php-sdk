<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Payment\PaymentWithReceiptBuilder;
use Tmconsulting\Uniteller\Receipt\Receipt;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\PaymentWithReceiptBuilder
 */
class PaymentWithReceiptBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $signatureCreator->method('setFields')->willReturnSelf();
        $signatureCreator->method('createSha256')->willReturn('mocked_signature');
        $this->builder = new PaymentWithReceiptBuilder($signatureCreator);
    }

    public function testSetAndGetReceipt()
    {
        $receipt = $this->createMock(Receipt::class);
        $this->builder->setReceipt($receipt);

        $this->assertSame($receipt, $this->builder->getReceipt());
    }

    public function testGetReceiptThrowsExceptionIfNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->builder->getReceipt();
    }

    public function testToArray()
    {
        $receiptMock = $this->createMock(Receipt::class);
        $receiptMock->method('toBase64')->willReturn('encoded_receipt');
        $this->builder->setReceipt($receiptMock)
            ->setShopId('shop')
            ->setOrderId('order123')
            ->setSubtotalP(500)
            ->setPassword('secret')
            ->setUrlReturnOk('https://ok.url')
            ->setUrlReturnNo('https://no.url');

        $arr = $this->builder->toArray();

        $this->assertArrayHasKey('Shop_IDP', $arr);
        $this->assertArrayHasKey('Order_IDP', $arr);
        $this->assertArrayHasKey('Subtotal_P', $arr);
        $this->assertArrayHasKey('Receipt', $arr);
        $this->assertEquals('encoded_receipt', $arr['Receipt']);
        $this->assertEquals('mocked_signature', $arr['ReceiptSignature']);
        $this->assertEquals('mocked_signature', $this->builder->getReceiptSignature());
    }

    public function testGetRequestName()
    {
        $this->assertEquals('v2/pay', $this->builder->getEndpoint());
    }
}