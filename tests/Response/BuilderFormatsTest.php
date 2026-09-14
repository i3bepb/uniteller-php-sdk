<?php

namespace Tmconsulting\Uniteller\Tests\Response;

use GuzzleHttp\Psr7\Response;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Tests\ResponseTestCase;

class BuilderFormatsTest extends ResponseTestCase
{
    /** @dataProvider supportedFormats */
    public function testSerializesCodeAndUsesSelectedResponseFormat(string $class, string $format, int $code)
    {
        $body = $format === Format::CSV
            ? "OrderNumber;Status\norder123;authorized"
            : '<unitellerresult><orders><order><ordernumber>order123</ordernumber></order></orders></unitellerresult>';
        $container = $this->responseContainer([new Response(200, [], $body)], function ($request) use ($format, $code) {
            parse_str((string)$request->getBody(), $parameters);
            $this->assertSame((string)$code, $parameters['Format']);
            $this->assertSame(
                $format === Format::CSV ? 'text/csv' : 'application/xml', $request->getHeaderLine('Accept')
            );
        });
        $builder = $container->get($class)->setShopId('shop')->setLogin('login')->setPassword('password');
        if ($builder instanceof ConfirmBuilder) {
            $builder->setBillNumber('123456789012');
        }

        $this->assertSame($builder, $builder->setFormat($format));
        $this->assertSame($format, $builder->getResponseFormat());
        $this->assertSame($code, $builder->toArray()['Format']);
        $orders = $builder->process();
        $this->assertInstanceOf(Order::class, $orders[0]);
        $this->assertSame('order123', $orders[0]->getOrderNumber());
    }

    public function supportedFormats(): array
    {
        return [
            [ConfirmBuilder::class, Format::CSV, 1],
            [ConfirmBuilder::class, Format::XML, 3],
            [ResultsBuilder::class, Format::CSV, 1],
            [ResultsBuilder::class, Format::XML, 4],
            [FiscalResultsBuilder::class, Format::CSV, 1],
            [FiscalResultsBuilder::class, Format::XML, 4],
        ];
    }

    /** @dataProvider unsupportedFormats */
    public function testRejectsUnsupportedFormatBeforeSendingRequest(string $class, string $format)
    {
        $container = $this->responseContainer([]);
        $builder = $container->get($class);

        $this->expectException(NotValidParameterException::class);
        $builder->setFormat($format);
    }

    public function unsupportedFormats(): array
    {
        return [
            [ConfirmBuilder::class, Format::SOAP],
            [ConfirmBuilder::class, Format::BRACKETS],
            [ConfirmBuilder::class, Format::JSON],
            [ResultsBuilder::class, Format::JSON],
            [FiscalResultsBuilder::class, Format::JSON],
        ];
    }
}
