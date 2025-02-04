<?php

namespace Tmconsulting\Uniteller\Tests\Signature;

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;

class SignatureLoggingTest extends TestCase
{
    /** @dataProvider verificationParameters */
    public function testVerifyLogsMaskedParametersAndBothSignatures(array $parameters, array $masked)
    {
        $signature = (new Signature())->setParameters($parameters);
        $expected = strtoupper(md5(implode('', $parameters)));
        $entries = [];
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))->method('debug')->willReturnCallback(
            function ($message, array $context) use (&$entries) {
                $entries[] = [$message, $context];
            }
        );
        $signature->setLogger($logger);

        // По умолчанию debug выключен.
        $this->assertTrue($signature->verify($expected));
        $signature->setDebug(true);
        $this->assertTrue($signature->verify($expected));
        $this->assertFalse($signature->verify('INVALID_SIGNATURE'));
        $signature->setDebug(false);
        // Маскирование лога не должно менять параметры следующих проверок.
        $this->assertTrue($signature->verify($expected));

        foreach ($entries as $index => [$message, $context]) {
            $this->assertSame('Signature verify md5', $message);
            $this->assertSame($masked, $context['parameters']);
            $this->assertSame($expected, $context['expected_signature']);
            $this->assertSame($index === 0 ? $expected : 'INVALID_SIGNATURE', $context['received_signature']);
            $this->assertSame($index === 0, $context['valid']);
            $this->assertStringNotContainsString('verify-secret-7Q', json_encode($context));
            $this->assertStringNotContainsString(md5('verify-secret-7Q'), json_encode($context));
        }
    }

    public function verificationParameters(): array
    {
        return [
            'named callback parameters' => [
                ['Order_ID' => '12345', 'Status' => 'authorized', 'Total' => '50.00', 'Password' => 'verify-secret-7Q'],
                ['Order_ID' => '12345', 'Status' => 'authorized', 'Total' => '50.00', 'Password' => '*****'],
            ],
            'legacy positional parameters' => [
                ['12345', 'authorized', '50.00', 'verify-secret-7Q'],
                ['12345', 'authorized', '50.00', '*****'],
            ],
            'named password not last' => [
                ['Password' => 'verify-secret-7Q', 'Order_ID' => '12345'],
                ['Password' => '*****', 'Order_ID' => '12345'],
            ],
            'empty parameters' => [[], []],
        ];
    }

    public function testVerifyWithDebugEnabledWithoutLogger()
    {
        $signature = (new Signature())->setParameters(['value']);
        $signature->setDebug(true);

        $this->assertTrue($signature->verify(strtoupper(md5('value'))));
    }

    /** @dataProvider signatureParameters */
    public function testDebugMasksOnlyRawPasswordAndPreservesHashes(string $method, string $algorithm, array $parameters)
    {
        $signature = (new Signature())->setParameters($parameters);
        $expected = $signature->$method();
        $message = null;
        $context = null;
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug')->willReturnCallback(
            function ($entry, array $data) use (&$message, &$context) {
                $message = $entry;
                $context = $data;
            }
        );
        $signature->setLogger($logger);
        $signature->setDebug(true);

        $this->assertSame($expected, $signature->$method());
        $this->assertSame('Signature ' . $algorithm, $message);
        $this->assertSame(['parameters', 'hashes', 'signature'], array_keys($context));
        $masked = $parameters;
        if (array_key_exists('Password', $parameters)) {
            $masked['Password'] = '*****';
            $this->assertSame(hash($algorithm, $parameters['Password']), $context['hashes']['Password']);
        }
        $this->assertSame($masked, $context['parameters']);
        $this->assertSame(array_keys($parameters), array_keys($context['hashes']));
        foreach ($parameters as $name => $value) {
            $this->assertSame(hash($algorithm, $value), $context['hashes'][$name]);
        }
        $this->assertSame($expected, $context['signature']);
        $this->assertSame($expected, strtoupper(hash($algorithm, implode('&', $context['hashes']))));
        $this->assertStringNotContainsString('log-secret-9D', json_encode($context));

        $signature->setDebug(false);
        $this->assertSame($expected, $signature->$method());
    }

    public function signatureParameters(): array
    {
        $cases = [];
        foreach (['createMd5' => 'md5', 'createSha256' => 'sha256'] as $method => $algorithm) {
            $cases[$algorithm . ' password'] = [$method, $algorithm, [
                'ShopID' => 'shop123', 'Password' => 'log-secret-9D', 'OrderID' => 'order123',
            ]];
            $cases[$algorithm . ' zero password'] = [$method, $algorithm, [
                'ShopID' => 'shop123', 'Password' => '0', 'OrderID' => 'order123',
            ]];
            $cases[$algorithm . ' no password'] = [$method, $algorithm, ['OrderID' => 'order123']];
            $cases[$algorithm . ' empty parameters'] = [$method, $algorithm, []];
        }
        return $cases;
    }
}
