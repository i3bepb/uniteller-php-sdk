<?php

namespace Tmconsulting\Uniteller\Tests\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ErrorException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Request\DecodedResponse;
use Tmconsulting\Uniteller\Response\LegacyCsvResponseParser;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Response\LegacyCsvResponseParser
 */
class LegacyCsvResponseParserTest extends TestCase
{
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
     */
    private $parserReceiptMock;

    /**
     * @var \Tmconsulting\Uniteller\Response\LegacyCsvResponseParser
     */
    private $parserCsv;

    protected function setUp(): void
    {
        $this->parserReceiptMock = $this->createMock(ParserReceiptFromBase64::class);
        $this->parserCsv = new LegacyCsvResponseParser($this->parserReceiptMock);
    }

    public function testParseOrdersWithEmptyData()
    {
        $result = $this->parseOrders([]);
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

        $result = $this->parseOrders($orderData);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Order::class, $result[0]);

        $order = $result[0];
        $this->assertSame('123', $order->getOrderNumber());
        $this->assertSame(['receipt_data'], $order->getReceipts());
        $this->assertTrue($order->isCvc2());
        $this->assertSame('10', $order->getBillNumber());
        $this->assertSame(20, $order->getErrorCode());
        $this->assertSame(1, $order->getPaymentType());
        $this->assertTrue($order->isOtherCard());
        $this->assertTrue($order->isNeedConfirm());
        $this->assertSame(5, $order->getGdsPaymentPurposeId());
        $this->assertSame('100.50', $order->getSum());
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

        $result = $this->parseOrders($orderData);

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

        $result = $this->parseOrders($orderData);
        $this->assertNull($result[0]->getReceipts());
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

        $this->parseErrors($errorData, $requestMock, $responseMock);
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

        $this->parseErrors($errorData, $requestMock, $responseMock);
    }

    public function testParseErrorsWithoutErrors()
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);

        $this->parseErrors([], $requestMock, $responseMock);
        $this->parseErrors([['OtherField' => 'value']], $requestMock, $responseMock);

        // Если не было исключения, тест пройден
        $this->assertTrue(true);
    }

    private function parseOrders(array $data): array
    {
        return $this->parserCsv->parse(new DecodedResponse(
            $data,
            $this->createMock(RequestInterface::class),
            $this->createMock(ResponseInterface::class)
        ));
    }

    private function parseErrors(array $data, RequestInterface $request, ResponseInterface $response): void
    {
        $this->parserCsv->parse(new DecodedResponse($data, $request, $response));
    }
}
