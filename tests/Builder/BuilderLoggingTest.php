<?php

namespace Tmconsulting\Uniteller\Tests\Builder;

use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Cancel\FiscalCancelBuilder;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Exception\AuthenticationException;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Support\LoggingPolicy;
use Tmconsulting\Uniteller\Tests\ResponseTestCase;

class BuilderLoggingTest extends ResponseTestCase
{
    /** @dataProvider builders */
    public function testProcessMasksSensitiveParametersOutsideSignatureCalculation(string $class)
    {
        $password = 'log-secret-9D';
        $body = "OrderNumber;Status\norder123;paid";
        if ($class === FiscalConfirmBuilder::class) {
            $body = '<response><Result>11</Result><ErrorMessage>Invalid ReceiptSignature</ErrorMessage></response>';
        } elseif ($class === FiscalCancelBuilder::class) {
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
            function ($message, array $context) use (&$messages) {
                $this->assertSame(['uniteller'], array_keys($context));
                $context = $context['uniteller'];
                // Полная диагностика расчёта подписи проверяется отдельным тестом.
                if (in_array($message, ['uniteller.signature.calculated'], true)) {
                    return;
                }
                $messages[] = $message . PHP_EOL . print_r($context, true);
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
        $events = [
            PaymentBuilder::class       => 'uniteller.payment_form.url_generated',
            FiscalCancelBuilder::class  => 'uniteller.cancel.request_prepared',
            ConfirmBuilder::class       => 'uniteller.confirm.request_prepared',
            FiscalConfirmBuilder::class => 'uniteller.fiscal_confirm.request_prepared',
            RecurrentBuilder::class     => 'uniteller.recurrent.request_prepared',
            ResultsBuilder::class       => 'uniteller.results.request_prepared',
            FiscalResultsBuilder::class => 'uniteller.fiscal_results.request_prepared',
        ];
        $this->assertStringContainsString($events[$class], $log);
        if (isset($expectedParameters['Password'])) {
            $this->assertStringContainsString('[Password] => [REDACTED]', $log);
        }
        $this->assertStringNotContainsString($password, $log);
        $this->assertStringNotContainsString(md5($password), $log);
        $this->assertStringNotContainsString(hash('sha256', $password), $log);
        $this->assertStringContainsString('shop123', $log);
        if (isset($expectedParameters['Receipt'])) {
            $this->assertStringContainsString('[Receipt] => [REDACTED]', $log);
            $this->assertStringNotContainsString($expectedParameters['Receipt'], $log);
            $this->assertStringNotContainsString(hash('sha256', $expectedParameters['Receipt']), $log);
        }
        if ($builder instanceof FiscalConfirmBuilder) {
            $this->assertStringContainsString('uniteller.fiscal_confirm.receipt_prepared', $log);
            $this->assertStringContainsString('[Receipt] => [REDACTED]', $log);
        }
        $this->assertSame($password, $builder->getPassword());
        $this->assertSame($expectedParameters, $builder->toArray());
        if ($builder instanceof PaymentBuilder) {
            $this->assertSame(
                $builder->getEndpoint() . '?' . http_build_query($expectedParameters),
                $result->getUrl()
            );
        }
    }

    public function testPaymentSignatureLogsOriginalNamedParametersAndTheirHashes(): void
    {
        foreach ([true, false] as $mask) {
            $container = $this->responseContainer([]);
            $container->set(LoggingPolicy::class, new LoggingPolicy($mask));
            $context = null;
            $logger = $this->createMock(LoggerInterface::class);
            $logger->expects($this->once())->method('debug')->willReturnCallback(
                function ($message, array $entry) use (&$context) {
                    $this->assertSame('uniteller.signature.calculated', $message);
                    $this->assertSame(['uniteller'], array_keys($entry));
                    $context = $entry['uniteller'];
                }
            );
            $container->set(LoggerInterface::class, $logger);
            $builder = $container->get(PaymentBuilder::class)
                ->setShopId('shop123')->setOrderId('order123')->setSubtotal('20.00')
                ->setCustomerId('customer-123')->setPhoneVerified('+79991234567')->setPassword('named-secret');
            $expectedSignature = $builder->getSignature();
            $container->get(Signature::class)->setDebug(true);

            $this->assertSame($expectedSignature, $builder->getSignature());
            $this->assertSame($expectedSignature, $context['signature']);
            $this->assertSame(array_keys($context['parameters']), array_keys($context['hashes']));
            $this->assertSame('named-secret', $context['parameters'][UnitellerParameterName::PASSWORD]);
            $this->assertSame(md5('named-secret'), $context['hashes'][UnitellerParameterName::PASSWORD]);
            foreach ([UnitellerParameterName::CUSTOMER_IDP => 'customer-123', UnitellerParameterName::PHONE_VERIFIED => '+79991234567'] as $field => $value) {
                $this->assertSame($value, $context['parameters'][$field]);
                $this->assertSame(md5($value), $context['hashes'][$field]);
            }
            $this->assertSame('shop123', $context['parameters'][UnitellerParameterName::SHOP_IDP]);
            $this->assertSame(md5('shop123'), $context['hashes'][UnitellerParameterName::SHOP_IDP]);
            $this->assertSame('named-secret', $builder->getPassword());
            $this->assertSame('+79991234567', $builder->getPhoneVerified());
        }
    }

    /** @dataProvider failingBuilders */
    public function testProcessLogsDiagnosticContextAndRethrowsTheOriginalError(
        string $class,
        \Throwable $exception,
        string $expectedMessage
    ): void
    {
        $requestManager = $this->createMock(RequestManager::class);
        $requestManager->expects($this->once())->method('executeRequest')->with($this->isInstanceOf($class))
            ->willThrowException($exception);
        $container = $this->responseContainer([]);
        $container->set(RequestManager::class, $requestManager);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with(
            $this->callback(function ($message) use ($class) {
                $events = [
                    FiscalCancelBuilder::class  => 'uniteller.cancel.failed',
                    ConfirmBuilder::class       => 'uniteller.confirm.failed',
                    FiscalConfirmBuilder::class => 'uniteller.fiscal_confirm.failed',
                    RecurrentBuilder::class     => 'uniteller.recurrent.failed',
                    ResultsBuilder::class       => 'uniteller.results.failed',
                    FiscalResultsBuilder::class => 'uniteller.fiscal_results.failed',
                ];
                return $message === $events[$class];
            }),
            $this->callback(function (array $context) use ($exception, $expectedMessage) {
                $this->assertSame(['uniteller' => ['error' => [
                    'class' => get_class($exception),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ], 'message' => $expectedMessage]], $context);
                $this->assertStringNotContainsString('secret-9D', json_encode($context));

                return true;
            })
        );
        $container->set(LoggerInterface::class, $logger);
        $builder = $container->get($class);

        try {
            $builder->process();
            $this->fail('Expected the original error to be rethrown');
        } catch (\Throwable $caught) {
            $this->assertSame($exception, $caught);
        }
    }

    public function failingBuilders(): array
    {
        $request = new \GuzzleHttp\Psr7\Request('POST', 'https://uniteller.test/pay?Password=secret-9D');
        $response = new Response(200, [], 'secret-9D response body');
        $failures = [
            'PHP error' => [new \TypeError('secret-9D buyer@example.test'), 'Uniteller operation failed'],
            'safe SDK message' => [
                new AuthenticationException('Authentication error', $request, $response),
                'Uniteller operation failed: Authentication error',
            ],
            'unsafe SDK message' => [
                new AuthenticationException('Authentication error: secret-9D buyer@example.test', $request, $response),
                'Uniteller operation failed',
            ],
        ];
        $cases = [];
        foreach ([FiscalCancelBuilder::class, ConfirmBuilder::class, FiscalConfirmBuilder::class,
            RecurrentBuilder::class, ResultsBuilder::class, FiscalResultsBuilder::class] as $class) {
            foreach ($failures as $name => [$exception, $message]) {
                $cases[$class . ' ' . $name] = [$class, $exception, $message];
            }
        }

        return $cases;
    }

    public function builders(): array
    {
        return [
            [PaymentBuilder::class],
            [ConfirmBuilder::class],
            [FiscalConfirmBuilder::class],
            [FiscalCancelBuilder::class],
            [RecurrentBuilder::class],
            [ResultsBuilder::class],
            [FiscalResultsBuilder::class],
        ];
    }
}
