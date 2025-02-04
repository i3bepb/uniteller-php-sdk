<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ErrorException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Request\ParserCsv
 */
class ParserCsvTest extends TestCase
{
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
     */
    private $parserReceiptMock;

    /**
     * @var \Tmconsulting\Uniteller\Request\ParserCsv
     */
    private $parserCsv;

    protected function setUp(): void
    {
        $this->parserReceiptMock = $this->createMock(ParserReceiptFromBase64::class);
        $this->parserCsv = new ParserCsv($this->parserReceiptMock);
    }

    public function testParseSimpleCsv()
    {
        $csvData = "field1,field2\nvalue1,value2";
        $result = $this->parserCsv->parse($csvData);

        $this->assertSame([
            ['field1' => 'value1', 'field2' => 'value2']
        ], $result);
    }

    public function testParseEmptyCsv()
    {
        $result = $this->parserCsv->parse('');
        $this->assertSame([], $result);
    }

    public function testParseOrdersWithEmptyData()
    {
        $result = $this->parserCsv->parseOrders([]);
        $this->assertSame([], $result);
    }

    public function testParseOrdersWithSingleItem()
    {
        $this->parserReceiptMock->method('parse')
            ->willReturn(['receipt_data']);

        $orderData = [
            [
                SFields::ORDER_NUMBER           => '123',
                SFields::RECEIPT                => 'base64receipt',
                SFields::CVC2                   => '1',
                SFields::BILL_NUMBER            => '10',
                SFields::ERROR_CODE             => '20',
                SFields::PAYMENT_TYPE           => '1',
                SFields::IS_OTHER_CARD          => '1',
                SFields::NEED_CONFIRM           => '1',
                SFields::GDS_PAYMENT_PURPOSE_ID => '5',
                SFields::SUM                    => '100.50',
                'Signature'                     => 'sig123'
            ]
        ];

        $result = $this->parserCsv->parseOrders($orderData);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Order::class, $result[0]);

        $order = $result[0];
        $this->assertSame('123', $order->getOrderNumber());
        $this->assertSame(['receipt_data'], $order->getReceipts());
        $this->assertTrue($order->isCvc2());
        $this->assertSame(10, $order->getBillNumber());
        $this->assertSame(20, $order->getErrorCode());
        $this->assertSame(1, $order->getPaymentType());
        $this->assertTrue($order->isOtherCard());
        $this->assertTrue($order->isNeedConfirm());
        $this->assertSame(5, $order->getGdsPaymentPurposeId());
        $this->assertSame(100.50, $order->getSum());
        $this->assertSame('sig123', $order->getSignature());
    }

    public function testParseOrdersWithMultipleItems()
    {
        $this->parserReceiptMock->method('parse')
            ->willReturn([]);

        $orderData = [
            [
                SFields::ORDER_NUMBER => '123',
                SFields::MESSAGE      => 'Message 1'
            ],
            [
                SFields::ORDER_NUMBER => '456',
                SFields::MESSAGE      => 'Message 2'
            ]
        ];

        $result = $this->parserCsv->parseOrders($orderData);

        $this->assertCount(2, $result);
        $this->assertSame('123', $result[0]->getOrderNumber());
        $this->assertSame('Message 1', $result[0]->getMessage());
        $this->assertSame('456', $result[1]->getOrderNumber());
        $this->assertSame('Message 2', $result[1]->getMessage());
    }

    public function testParseOrdersWithoutReceipt()
    {
        $this->parserReceiptMock->expects($this->never())
            ->method('parse');

        $orderData = [
            [SFields::ORDER_NUMBER => '123']
        ];

        $result = $this->parserCsv->parseOrders($orderData);
        $this->assertSame([], $result[0]->getReceipts());
    }

    public function testParseErrorsWithErrorCodeAndMessage()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $errorData = [
            ['ErrorCode' => '100', 'ErrorMessage' => 'Test error']
        ];

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Test error');

        $this->parserCsv->parseErrors($errorData, $requestMock, $responseMock);
    }

    public function testParseErrorsWithOnlyErrorMessage()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $errorData = [
            ['ErrorMessage' => 'Generic error']
        ];

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Generic error');

        $this->parserCsv->parseErrors($errorData, $requestMock, $responseMock);
    }

    public function testParseErrorsWithoutErrors()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $this->parserCsv->parseErrors([], $requestMock, $responseMock);
        $this->parserCsv->parseErrors([['OtherField' => 'value']], $requestMock, $responseMock);

        // Если не было исключения, тест пройден
        $this->assertTrue(true);
    }
}
