<?php

namespace Tmconsulting\Uniteller\Tests\Response;

use GuzzleHttp\Psr7\Response;
use Tmconsulting\Uniteller\Cancel\FiscalCancelBuilder;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Exception\AuthenticationException;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Receipt\FiscalReceipt;
use Tmconsulting\Uniteller\Receipt\Enum\Kind;
use Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod;
use Tmconsulting\Uniteller\Tests\ResponseTestCase;

class BuilderResponsesTest extends ResponseTestCase
{
    /** @dataProvider legacyBuilders */
    public function testLegacyCsvReturnsOrders(string $class)
    {
        $body = "OrderNumber;Status;BillNumber;Error_Code\norder123;authorized;00001234;0\norder456;paid;00001235;0";
        $container = $this->responseContainer([new Response(200, [], $body)], function ($request) {
            $this->assertSame('text/csv', $request->getHeaderLine('Accept'));
        });
        $builder = $container->get($class)->setShopId('shop')->setPassword('password');
        if ($class === RecurrentBuilder::class) {
            $builder->setOrderId('order123')->setParentOrderId('parent')->setSubtotal('20.00');
        } else {
            $builder->setLogin('login');
            if ($builder instanceof ResultsBuilder) {
                $builder->setFormat(Format::CSV);
            }
        }
        if ($class === ConfirmBuilder::class) {
            $builder->setBillNumber('00001234');
        }
        $orders = $builder->process();
        $this->assertCount(2, $orders);
        $this->assertInstanceOf(Order::class, $orders[0]);
        $this->assertSame('order123', $orders[0]->getOrderNumber());
        $this->assertSame('00001234', $orders[0]->getBillNumber());
        $this->assertSame('order456', $orders[1]->getOrderNumber());
        $this->assertNull($orders[0]->getReceipts());
    }

    public function legacyBuilders(): array
    {
        return [[ResultsBuilder::class], [FiscalResultsBuilder::class], [ConfirmBuilder::class], [RecurrentBuilder::class]];
    }

    /** @dataProvider legacyErrors */
    public function testLegacyErrorPreservesExceptionAndHttpContext(string $format, string $body)
    {
        $response = new Response(200, [], $body);
        $container = $this->responseContainer([$response]);
        $builder = $container->get(ResultsBuilder::class)->setShopId('shop')->setLogin('login')->setPassword('password')->setFormat($format);
        try {
            $builder->process();
            $this->fail('Expected legacy authentication exception');
        } catch (AuthenticationException $e) {
            $this->assertSame('Authentication error', $e->getMessage());
            $this->assertSame($response, $e->getResponse());
            $this->assertSame('POST', $e->getRequest()->getMethod());
        }
    }

    public function legacyErrors(): array
    {
        return [
            [Format::CSV, "ErrorCode;ErrorMessage\n1;Authentication error"],
            [Format::CSV, "ErrorMessage\nAuthentication error"],
            [Format::XML, '<response><ErrorMessage>Authentication error</ErrorMessage></response>'],
            [Format::XML, '<response><ErrorCode>1</ErrorCode><ErrorMessage>Authentication error</ErrorMessage></response>'],
            [Format::XML, '<response><Result>1</Result><ErrorMessage>Authentication error</ErrorMessage></response>'],
        ];
    }

    public function testSwitchingFormatsInOneContainer()
    {
        $container = $this->responseContainer([
            new Response(200, [], "OrderNumber;Status\nfirst;authorized"),
            new Response(200, [], '<response><Result>11</Result><ErrorMessage>Invalid ReceiptSignature</ErrorMessage></response>'),
            new Response(200, [], $this->getStubContents('results')),
            new Response(200, [], "OrderNumber;Status\nlast;paid"),
        ]);
        $builder = $container->get(ResultsBuilder::class)->setShopId('shop')->setLogin('login')->setPassword('password')->setFormat(Format::CSV);
        $this->assertSame('first', $builder->process()[0]->getOrderNumber());
        $this->assertSame(11, $this->fiscalBuilder($container)->process()->getResult());
        $builder->setFormat(Format::XML);
        $this->assertCount(2, $builder->process());
        $builder->setFormat(Format::CSV);
        $this->assertSame('last', $builder->process()[0]->getOrderNumber());
    }

    public function testCancelJsonStillReturnsArrayWithReceipts()
    {
        $expectedReceipt = null;
        $container = $this->responseContainer(
            [new Response(200, [], $this->getStubContents('responseCancelWithReceipt', 'json'))],
            function ($request) use (&$expectedReceipt) {
                parse_str((string)$request->getBody(), $parameters);
                $this->assertSame($expectedReceipt, $parameters['Receipt']);
                $expectedSignature = strtoupper(hash('sha256',
                    hash('sha256', 'order123') . '&' . hash('sha256', 'shop') . '&'
                    . hash('sha256', '20.00') . '&' . hash('sha256', $expectedReceipt) . '&'
                    . hash('sha256', 'password')
                ));
                $this->assertSame($expectedSignature, $parameters['Signature']);
            }
        );
        $builder = $container->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password')->setSubtotal('20.00');
        // ReceiptParameter accepts the receipt domain object, not its Base64 representation.
        $receipts = (new \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64())->parse(base64_encode(json_encode($this->receiptData())));
        $builder->setReceipt($receipts[0]);
        $expectedReceipt = $builder->getReceipt();
        $result = $builder->process();
        $this->assertSame('00', $result['Code']);
        $this->assertSame('canceled', $result['Status']);
        $this->assertInstanceOf(FiscalReceipt::class, $result['Receipt'][0]);
    }

    /** @dataProvider zeroSubtotals */
    public function testCancelAdditionalPaymentWithReceiptIncludesZeroInRequestAndSignature($subtotal): void
    {
        $expectedReceipt = null;
        $container = $this->responseContainer(
            [new Response(200, [], $this->getStubContents('responseCancelWithReceipt', 'json'))],
            function ($request) use (&$expectedReceipt, $subtotal) {
                parse_str((string)$request->getBody(), $parameters);
                $this->assertCount(5, $parameters);
                $this->assertSame((string)$subtotal, $parameters['Subtotal']);
                $this->assertSame($expectedReceipt, $parameters['Receipt']);
                $expectedSignature = strtoupper(hash('sha256',
                    hash('sha256', 'order123') . '&' . hash('sha256', 'shop') . '&'
                    . hash('sha256', (string)$subtotal) . '&' . hash('sha256', $expectedReceipt) . '&'
                    . hash('sha256', 'password')
                ));
                $this->assertSame($expectedSignature, $parameters['Signature']);
            }
        );
        $receiptData = $this->receiptData()[0];
        $receiptData['payments'] = [[
            'kind' => Kind::ADDITIONAL, 'type' => TypePaymentMethod::CASH, 'amount' => $receiptData['total'],
        ]];
        $receipts = (new \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64())->parse(base64_encode(json_encode([$receiptData])));
        $builder = $container->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password')
            ->setSubtotal($subtotal)->setReceipt($receipts[0]);
        $expectedReceipt = $builder->getReceipt();

        $this->assertTrue($builder->hasSubtotal());
        $this->assertSame('00', $builder->process()['Code']);
    }

    /** @dataProvider zeroSubtotals */
    public function testCancelZeroSubtotalWithoutReceiptIsRejectedBeforeSending($subtotal): void
    {
        $builder = $this->responseContainer([])->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password')->setSubtotal($subtotal);

        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Invalid Subtotal: amount must be greater than zero without Receipt.');
        $builder->process();
    }

    public function zeroSubtotals(): array
    {
        return [[0], ['0'], ['0.0'], ['0.00']];
    }

    public function testCancelFullPaymentWithReceiptOmitsSubtotalFromRequestAndSignature(): void
    {
        $expectedReceipt = null;
        $container = $this->responseContainer(
            [new Response(200, [], $this->getStubContents('responseCancelWithReceipt', 'json'))],
            function ($request) use (&$expectedReceipt) {
                parse_str((string)$request->getBody(), $parameters);
                $this->assertCount(4, $parameters);
                $this->assertSame('order123', $parameters['OrderID']);
                $this->assertSame('shop', $parameters['UPID']);
                $this->assertSame($expectedReceipt, $parameters['Receipt']);
                $this->assertArrayNotHasKey('Subtotal', $parameters);
                $this->assertArrayNotHasKey('Password', $parameters);
                $expectedSignature = strtoupper(hash('sha256',
                    hash('sha256', 'order123') . '&' . hash('sha256', 'shop') . '&'
                    . hash('sha256', $expectedReceipt) . '&' . hash('sha256', 'password')
                ));
                $this->assertSame($expectedSignature, $parameters['Signature']);
            }
        );
        $receipts = (new \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64())->parse(base64_encode(json_encode($this->receiptData())));
        $builder = $container->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password')->setReceipt($receipts[0]);
        $expectedReceipt = $builder->getReceipt();

        $this->assertFalse($builder->hasSubtotal());
        $result = $builder->process();
        $this->assertSame('00', $result['Code']);
        $this->assertSame('canceled', $result['Status']);
        $this->assertInstanceOf(FiscalReceipt::class, $result['Receipt'][0]);
    }

    public function testCancelWithoutReceiptRequiresSubtotal(): void
    {
        $builder = $this->responseContainer([])->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password');

        $this->expectException(\Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Subtotal empty or not set');
        $builder->process();
    }

    public function testCancelPreauthorizationWithoutReceiptSendsSignedRequestAndParsesJson(): void
    {
        $responseData = ['Code' => '00', 'Note' => 'Operation completed', 'Status' => 'canceled', 'Balance' => '0.00'];
        $container = $this->responseContainer(
            [new Response(200, ['Content-Type' => 'application/json'], json_encode($responseData))],
            function ($request) {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame(ApiEndpoints::FISCAL_CANCEL, (string)$request->getUri());
                $this->assertSame('application/json', $request->getHeaderLine('Accept'));
                parse_str((string)$request->getBody(), $parameters);
                $this->assertCount(4, $parameters);
                $this->assertSame('order123', $parameters['OrderID']);
                $this->assertSame('shop', $parameters['UPID']);
                $this->assertSame('20.00', $parameters['Subtotal']);
                $this->assertArrayNotHasKey('Receipt', $parameters);
                $this->assertArrayNotHasKey('Password', $parameters);
                $expectedSignature = strtoupper(hash('sha256',
                    hash('sha256', 'order123') . '&' . hash('sha256', 'shop') . '&'
                    . hash('sha256', '20.00') . '&' . hash('sha256', 'password')
                ));
                $this->assertSame($expectedSignature, $parameters['Signature']);
            }
        );
        $builder = $container->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password')->setSubtotal('20.00');

        $this->assertFalse($builder->hasReceipt());
        $this->assertSame($responseData, $builder->process());
        $this->assertFalse($builder->hasReceipt());
    }

    public function testFiscalCsvResultsDecodeReceiptAfterFormatParsing()
    {
        $receipt = base64_encode(json_encode($this->receiptData()));
        $container = $this->responseContainer([new Response(200, [], "OrderNumber;Receipt\norder123;" . $receipt)]);
        $builder = $container->get(FiscalResultsBuilder::class)
            ->setShopId('shop')->setLogin('login')->setPassword('password')->setFormat(Format::CSV);
        $orders = $builder->process();
        $this->assertInstanceOf(Order::class, $orders[0]);
        $this->assertInstanceOf(FiscalReceipt::class, $orders[0]->getReceipts()[0]);
    }

    public function testCancelJsonErrorPreservesLegacyException()
    {
        $response = new Response(200, [], '{"Code":"1","Note":"Authentication error"}');
        $container = $this->responseContainer([$response]);
        $receipts = (new \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64())->parse(base64_encode(json_encode($this->receiptData())));
        $builder = $container->get(FiscalCancelBuilder::class)
            ->setShopId('shop')->setOrderId('order123')->setPassword('password')->setSubtotal('20.00')->setReceipt($receipts[0]);
        $this->expectException(AuthenticationException::class);
        $builder->process();
    }

}
