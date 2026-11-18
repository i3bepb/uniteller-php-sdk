<?php

namespace Tmconsulting\Uniteller\Tests\Confirm;

use GuzzleHttp\Psr7\Response;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmResult;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmResultParser;
use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\RequestException;
use Tmconsulting\Uniteller\Exception\ServerErrorException;
use Tmconsulting\Uniteller\Receipt\FiscalReceipt;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Tests\ResponseTestCase;

class FiscalConfirmResponseTest extends ResponseTestCase
{
    /** @dataProvider explicitFormats */
    public function testExplicitFormatIsSentAndSelectsResponseParser(string $format, string $code, string $accept): void
    {
        $receipt = base64_encode(json_encode($this->receiptData()));
        $body = $format === Format::CSV
            ? "Result;ErrorMessage;Receipt\n11;Invalid ReceiptSignature;" . $receipt
            : '<response><Result>11</Result><ErrorMessage>Invalid ReceiptSignature</ErrorMessage><Receipt>' . $receipt . '</Receipt></response>';
        $container = $this->responseContainer([new Response(200, ['Content-Type' => $accept], $body)], function ($request) use ($code, $accept) {
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame(ApiEndpoints::FISCAL_CONFIRM, (string)$request->getUri());
            $this->assertSame($accept, $request->getHeaderLine('Accept'));
            parse_str((string)$request->getBody(), $parameters);
            $this->assertSame($code, $parameters['Format']);
            if ($code === '1') {
                $this->assertSame('1', $parameters['Header1']);
            } else {
                $this->assertArrayNotHasKey('Header1', $parameters);
            }
            $this->assertArrayHasKey('Signature', $parameters);
            $this->assertArrayHasKey('ReceiptSignature', $parameters);
            $this->assertArrayNotHasKey('Password', $parameters);
        });
        $builder = $this->fiscalBuilder($container);
        $signature = $builder->getSignature();
        $receiptSignature = $builder->getReceiptSignature();
        $this->assertSame($builder, $builder->setFormat($format));
        if ($format === Format::CSV) {
            $this->assertSame($builder, $builder->setHeader1(1));
        }
        $this->assertSame($format, $builder->getResponseFormat());
        $this->assertSame($signature, $builder->getSignature());
        $this->assertSame($receiptSignature, $builder->getReceiptSignature());

        $result = $builder->process();
        $this->assertSame(11, $result->getResult());
        $this->assertSame('Invalid ReceiptSignature', $result->getErrorMessage());
        $this->assertCount(1, $result->getReceipts());
    }

    public function explicitFormats(): array
    {
        return [[Format::CSV, '1', 'text/csv'], [Format::XML, '3', 'application/xml']];
    }

    /** @dataProvider unsupportedFormats */
    public function testUnsupportedFormatIsRejectedBeforeSending(string $format): void
    {
        $builder = $this->fiscalBuilder($this->responseContainer([]));
        $this->expectException(NotValidParameterException::class);
        $builder->setFormat($format);
    }

    public function unsupportedFormats(): array
    {
        return [[Format::BRACKETS], [Format::SOAP], [Format::JSON], ['unknown']];
    }

    public function testCsvWithMultipleResultsIsRejected(): void
    {
        $container = $this->responseContainer([new Response(200, [], "Result;ErrorMessage\n0;\n11;Invalid ReceiptSignature")]);
        $builder = $this->fiscalBuilder($container)->setFormat(Format::CSV);
        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('Expected one row in FiscalConfirm CSV response');
        $builder->process();
    }

    /** @dataProvider receiptCounts */
    public function testSuccessWithReceipts(int $count)
    {
        $receipts = [];
        for ($i = 0; $i < $count; $i++) {
            $receipt = $this->receiptData()[0];
            $receipt['fiscal']['id'] = 'receipt-' . $i;
            $receipts[] = $receipt;
        }
        $body = '<response><Result>0</Result><Receipt>' . base64_encode(json_encode($receipts)) . '</Receipt></response>';
        $container = $this->responseContainer([new Response(200, [], $body)], function ($request) {
            $this->assertSame(ApiEndpoints::FISCAL_CONFIRM, strtok((string)$request->getUri(), '?'));
            $this->assertSame('application/xml', $request->getHeaderLine('Accept'));
            parse_str((string)$request->getBody(), $parameters);
            $this->assertArrayNotHasKey('Format', $parameters);
            $this->assertArrayHasKey('ReceiptSignature', $parameters);
        });
        $result = $this->fiscalBuilder($container)->process();
        $this->assertInstanceOf(FiscalConfirmResult::class, $result);
        $this->assertSame(0, $result->getResult());
        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->getErrorMessage());
        $this->assertCount($count, $result->getReceipts());
        foreach ($result->getReceipts() as $i => $receipt) {
            $this->assertInstanceOf(FiscalReceipt::class, $receipt);
            $this->assertSame('receipt-' . $i, $receipt->getFiscal()->getId());
        }
    }

    public function receiptCounts(): array
    {
        return [[1], [2]];
    }

    /** @dataProvider businessErrors */
    public function testBusinessResult(int $code, string $message)
    {
        $body = '<response><Result>' . $code . '</Result><ErrorMessage>' . $message . '</ErrorMessage></response>';
        $result = $this->fiscalBuilder($this->responseContainer([new Response(200, [], $body)]))->process();
        $this->assertSame($code, $result->getResult());
        $this->assertFalse($result->isSuccess());
        $this->assertSame($message, $result->getErrorMessage());
        $this->assertSame([], $result->getReceipts());
    }

    public function businessErrors(): array
    {
        return [[11, 'Invalid ReceiptSignature'], [999, 'New Uniteller result']];
    }

    /** @dataProvider malformedResults */
    public function testMalformedResult(string $body)
    {
        $builder = $this->fiscalBuilder($this->responseContainer([new Response(200, [], $body)]));
        $this->expectException(InvalidResponseException::class);
        $builder->process();
    }

    public function malformedResults(): array
    {
        return [
            ['<response><ErrorMessage>Missing result</ErrorMessage></response>'],
            ['<response><Result/></response>'],
            ['<response><Result>garbage</Result></response>'],
            ['<response><Result>0.1</Result></response>'],
            ['<response><Result>0</Result><Result>11</Result></response>'],
            ['<response><Result>999999999999999999999999</Result></response>'],
            ['<response>'],
            [''],
        ];
    }

    /** @dataProvider brokenReceipts */
    public function testBrokenReceiptRemainsException(string $receipt, int $result)
    {
        $body = '<response><Result>' . $result . '</Result><Receipt>' . $receipt . '</Receipt></response>';
        $builder = $this->fiscalBuilder($this->responseContainer([new Response(200, [], $body)]));
        $this->expectException(\RuntimeException::class);
        $builder->process();
    }

    public function brokenReceipts(): array
    {
        return [['***', 0], [base64_encode('{'), 0], [base64_encode('null'), 0], ['', 0], ['***', 11]];
    }

    /** @dataProvider httpErrors */
    public function testHttpErrorOverridesBusinessResult(int $status, string $exception)
    {
        $body = '<response><Result>11</Result><ErrorMessage>Invalid ReceiptSignature</ErrorMessage></response>';
        $builder = $this->fiscalBuilder($this->responseContainer([new Response($status, [], $body)]));
        $this->expectException($exception);
        $builder->process();
    }

    public function httpErrors(): array
    {
        return [[400, RequestException::class], [500, ServerErrorException::class]];
    }

    public function testParsesAlreadyDecodedArrayIncludingReceiptsOnFailure()
    {
        $parser = new FiscalConfirmResultParser(new ParserReceiptFromBase64());
        $result = $parser->parse(['Result' => 54, 'Receipt' => base64_encode(json_encode($this->receiptData()))]);
        $this->assertFalse($result->isSuccess());
        $this->assertSame(54, $result->getResult());
        $this->assertNull($result->getErrorMessage());
        $this->assertCount(1, $result->getReceipts());
    }

    public function testSuccessWithoutReceiptAndEmptyMessage()
    {
        $parser = new FiscalConfirmResultParser(new ParserReceiptFromBase64());
        $result = $parser->parse(['Result' => '0', 'ErrorMessage' => []]);
        $this->assertTrue($result->isSuccess());
        $this->assertSame('', $result->getErrorMessage());
        $this->assertSame([], $result->getReceipts());
    }
}
