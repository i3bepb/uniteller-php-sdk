<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ErrorException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Request\ParserXml;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Request\ParserXml
 */
class ParserXmlTest extends TestCase
{
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
     */
    private $parserReceiptMock;

    /**
     * @var \Tmconsulting\Uniteller\Request\ParserXml
     */
    private $parserXml;

    protected function setUp(): void
    {
        $this->parserReceiptMock = $this->createMock(ParserReceiptFromBase64::class);
        $this->parserXml = new ParserXml($this->parserReceiptMock);
    }

    public function testParse()
    {
        $response = $this->getStubContents('results');
        $data = $this->parserXml->parse($response);
        $this->assertIsArray($data['orders']['order']);
        $this->assertCount(2, $data['orders']['order']);
    }

    public function testParseOrders()
    {
        $response = $this->getStubContents('results');
        $data = $this->parserXml->parse($response);
        $orders = $this->parserXml->parseOrders($data);
        $this->assertCount(2, $orders);
        $this->assertEquals('134.255.154.134', $orders[0]->getIp());
    }

    public function testParseSimpleXml()
    {
        $xml = '<?xml version="1.0"?><root><node>value</node></root>';
        $result = $this->parserXml->parse($xml);
        $this->assertArrayHasKey('node', $result);
        $this->assertSame('value', $result['node']);
    }

    public function testParseEmptyXml()
    {
        $result = $this->parserXml->parse('');
        $this->assertSame([], $result);
    }

    public function testParseOrdersWithEmptyData()
    {
        $result = $this->parserXml->parseOrders([]);
        $this->assertSame([], $result);
    }

    public function testParseOrdersWithSingleOrder()
    {
        $this->parserReceiptMock->method('parse')
            ->willReturn(['receipt_data']);

        $orderData = [
            'orders' => [
                'order' => [
                    [
                        'ordernumber' => '123',
                        'receipt' => 'base64receipt',
                        'cvc2' => '1',
                        'billnumber' => '10',
                        'error_code' => '20',
                        'paymenttype' => '1',
                        'isothercard' => '1',
                        'need_confirm' => '1',
                        'sum' => '100.50',
                        'signature' => 'sig123'
                    ]
                ]
            ]
        ];

        $result = $this->parserXml->parseOrders($orderData);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Order::class, $result[0]);

        /** @var \Tmconsulting\Uniteller\Order\Order $order */
        $order = $result[0];
        $this->assertSame('123', $order->getOrderNumber());
        $this->assertSame(['receipt_data'], $order->getReceipts());
        $this->assertTrue($order->isCvc2());
        $this->assertSame(10, $order->getBillNumber());
        $this->assertSame(20, $order->getErrorCode());
        $this->assertSame(1, $order->getPaymentType());
        $this->assertTrue($order->isOtherCard());
        $this->assertTrue($order->isNeedConfirm());
        $this->assertSame(100.50, $order->getSum());
        $this->assertSame('sig123', $order->getSignature());
    }

    public function testParseOrdersWithMultipleOrders()
    {
        $this->parserReceiptMock->method('parse')
            ->willReturn([]);

        $orderData = [
            'orders' => [
                'order' => [
                    [
                        'ordernumber' => '123',
                        'message' => 'Message 1'
                    ],
                    [
                        'ordernumber' => '456',
                        'message' => 'Message 2'
                    ]
                ]
            ]
        ];

        $result = $this->parserXml->parseOrders($orderData);

        $this->assertCount(2, $result);
        $this->assertSame('123', $result[0]->getOrderNumber());
        $this->assertSame('Message 1', $result[0]->getMessage());
        $this->assertSame('456', $result[1]->getOrderNumber());
        $this->assertSame('Message 2', $result[1]->getMessage());
    }

    public function testParseOrdersWithSingleOrderNotInArray()
    {
        $this->parserReceiptMock->method('parse')
            ->willReturn([]);

        $orderData = [
            'orders' => [
                'order' => [
                    'ordernumber' => '123',
                    'message' => 'Single order'
                ]
            ]
        ];

        $result = $this->parserXml->parseOrders($orderData);

        $this->assertCount(1, $result);
        $this->assertSame('123', $result[0]->getOrderNumber());
        $this->assertSame('Single order', $result[0]->getMessage());
    }

    public function testParseResultsWithReceipt()
    {
        $this->parserReceiptMock->method('parse')
            ->willReturn(['parsed_receipt']);

        $data = [
            'Receipt' => 'base64data',
            'OtherField' => 'value'
        ];

        $result = $this->parserXml->parseResults($data);

        $this->assertSame(['parsed_receipt'], $result['Receipt']);
        $this->assertSame('value', $result['OtherField']);
    }

    public function testParseResultsWithoutReceipt()
    {
        $this->parserReceiptMock->expects($this->never())
            ->method('parse');

        $data = [
            'Field' => 'value'
        ];

        $result = $this->parserXml->parseResults($data);
        $this->assertSame($data, $result);
    }

    public function testParseErrorsWithErrorCodeAndMessage()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $errorData = [
            'Result' => '100',
            'ErrorMessage' => 'Test error'
        ];

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Test error');

        $this->parserXml->parseErrors($errorData, $requestMock, $responseMock);
    }

    public function testParseErrorsWithOnlyErrorMessage()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $errorData = [
            'ErrorMessage' => 'Generic error'
        ];

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Generic error');

        $this->parserXml->parseErrors($errorData, $requestMock, $responseMock);
    }

    public function testParseErrorsWithoutErrors()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $this->parserXml->parseErrors([], $requestMock, $responseMock);
        $this->parserXml->parseErrors(['OtherField' => 'value'], $requestMock, $responseMock);

        // Если не было исключения, тест пройден
        $this->assertTrue(true);
    }
}
