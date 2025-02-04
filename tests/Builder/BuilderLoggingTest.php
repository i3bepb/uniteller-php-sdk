<?php

namespace Tmconsulting\Uniteller\Tests\Builder;

use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Cancel\CancelBuilder;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Tests\ResponseTestCase;

class BuilderLoggingTest extends ResponseTestCase
{
    /** @dataProvider builders */
    public function testProcessMasksPasswordWithoutChangingParameters(string $class)
    {
        $password = 'log-secret-9D';
        $body = "OrderNumber;Status\norder123;paid";
        if ($class === FiscalConfirmBuilder::class) {
            $body = '<response><Result>11</Result><ErrorMessage>Invalid ReceiptSignature</ErrorMessage></response>';
        } elseif ($class === CancelBuilder::class) {
            $body = $this->getStubContents('responseCancelWithReceipt', 'json');
        }
        $expectedParameters = [];
        $responses = $class === PaymentBuilder::class ? [] : [new Response(200, [], $body)];
        $container = $this->responseContainer($responses, function ($request) use (&$expectedParameters) {
            $this->assertSame(http_build_query($expectedParameters), (string)$request->getBody());
        });
        $messages = [];
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())->method('debug')->willReturnCallback(
            function ($message) use (&$messages) {
                $messages[] = $message;
            }
        );
        $container->set(LoggerInterface::class, $logger);
        $container->setDebug(true);
        $builder = $container->get($class)->setShopId('shop123')->setPassword($password);
        if ($builder instanceof ResultsBuilder) {
            $builder->setLogin('login')->setFormat(Format::CSV);
        } elseif ($builder instanceof ConfirmBuilder) {
            $builder->setLogin('login')->setBillNumber('00001234');
        } else {
            $builder->setOrderId('order123')->setSubtotal('20.00');
            if ($builder instanceof RecurrentBuilder) {
                $builder->setParentOrderId('parent123');
            } else {
                $receipt = (new ParserReceiptFromBase64())->parse(base64_encode(json_encode($this->receiptData())))[0];
                $builder->setReceipt($receipt);
            }
            if ($builder instanceof PaymentBuilder) {
                $builder->setUrlReturn('https://merchant.test/return');
            }
        }
        $expectedParameters = $builder->toArray();
        $messages = [];

        $result = $builder->process();

        $log = implode(PHP_EOL, $messages);
        $this->assertStringContainsString('Parameters in request:', $log);
        $this->assertStringContainsString('[Password] => *****', $log);
        $this->assertStringNotContainsString($password, $log);
        $this->assertStringNotContainsString(md5($password), $log);
        $this->assertStringNotContainsString(hash('sha256', $password), $log);
        $this->assertStringContainsString('shop123', $log);
        $this->assertSame($password, $builder->getPassword());
        $this->assertSame($expectedParameters, $builder->toArray());
        if ($builder instanceof PaymentBuilder) {
            $this->assertSame(
                $builder->getEndpoint() . '?' . http_build_query($expectedParameters),
                $result->getUri()
            );
        }
    }

    public function builders(): array
    {
        return [
            [PaymentBuilder::class],
            [ConfirmBuilder::class],
            [FiscalConfirmBuilder::class],
            [CancelBuilder::class],
            [RecurrentBuilder::class],
            [ResultsBuilder::class],
            [FiscalResultsBuilder::class],
        ];
    }
}
