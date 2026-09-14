<?php

namespace Tmconsulting\Uniteller\Tests\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ErrorException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Request\ParserXml;
use Tmconsulting\Uniteller\Request\DecodedResponse;
use Tmconsulting\Uniteller\Response\LegacyXmlResponseParser;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Response\LegacyXmlResponseParser
 */
class LegacyXmlResponseParserTest extends TestCase
{
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
     */
    private $parserReceiptMock;

    /**
     * @var \Tmconsulting\Uniteller\Response\LegacyXmlResponseParser
     */
    private $parserXml;

    protected function setUp(): void
    {
        $this->parserReceiptMock = $this->createMock(ParserReceiptFromBase64::class);
        $this->parserXml = new LegacyXmlResponseParser($this->parserReceiptMock);
    }

    public function testParseOrders()
    {
        $response = $this->getStubContents('results');
        $data = (new ParserXml())->parse($response);
        $orders = $this->parseOrders($data);
        $this->assertCount(2, $orders);
        $this->assertEquals('134.255.154.134', $orders[0]->getIp());
    }

    public function testParseOrdersWithEmptyData()
    {
        $result = $this->parseOrders([]);
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

        $result = $this->parseOrders($orderData);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Order::class, $result[0]);

        /** @var \Tmconsulting\Uniteller\Order\Order $order */
        $order = $result[0];
        $this->assertSame('123', $order->getOrderNumber());
        $this->assertSame(['receipt_data'], $order->getReceipts());
        $this->assertTrue($order->isCvc2());
        $this->assertSame('10', $order->getBillNumber());
        $this->assertSame(20, $order->getErrorCode());
        $this->assertSame(1, $order->getPaymentType());
        $this->assertTrue($order->isOtherCard());
        $this->assertTrue($order->isNeedConfirm());
        $this->assertSame('100.5', $order->getSum());
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

        $result = $this->parseOrders($orderData);

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

        $result = $this->parseOrders($orderData);

        $this->assertCount(1, $result);
        $this->assertSame('123', $result[0]->getOrderNumber());
        $this->assertSame('Single order', $result[0]->getMessage());
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

        $this->parseErrors($errorData, $requestMock, $responseMock);
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

        $this->parseErrors($errorData, $requestMock, $responseMock);
    }

    public function testParseErrorsWithoutErrors()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $this->parseErrors([], $requestMock, $responseMock);
        $this->parseErrors(['OtherField' => 'value'], $requestMock, $responseMock);

        // Если не было исключения, тест пройден
        $this->assertTrue(true);
    }

    private function parseOrders(array $data): array
    {
        return $this->parserXml->parse(new DecodedResponse(
            $data,
            $this->createMock(RequestInterface::class),
            $this->createMock(ResponseInterface::class)
        ));
    }

    private function parseErrors(array $data, RequestInterface $request, ResponseInterface $response): void
    {
        $this->parserXml->parse(new DecodedResponse($data, $request, $response));
    }
}
