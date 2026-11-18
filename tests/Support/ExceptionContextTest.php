<?php

namespace Tmconsulting\Uniteller\Tests\Support;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tmconsulting\Uniteller\Exception\AuthConfirmIsNotAllowedException;
use Tmconsulting\Uniteller\Exception\AuthenticationException;
use Tmconsulting\Uniteller\Exception\BadFieldFormatException;
use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Exception\OperationFailedException;
use Tmconsulting\Uniteller\Exception\RequestException;
use Tmconsulting\Uniteller\Support\ExceptionContext;

class ExceptionContextTest extends TestCase
{
    /** @dataProvider throwables */
    public function testOnlyDiagnosticFieldsAreIncluded(\Throwable $exception, string $expectedClass, int $expectedCode): void
    {
        $context = ExceptionContext::fromThrowable($exception);

        $this->assertSame([
            'class' => $expectedClass,
            'code' => $expectedCode,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], $context);
        $this->assertStringNotContainsString('secret', json_encode($context));
    }

    /** @dataProvider logMessages */
    public function testOnlyVerifiedFixedMessagesAreAppended(\Throwable $exception, ?string $expectedDetail): void
    {
        $fallback = 'Uniteller operation failed';
        $message = ExceptionContext::logMessage($exception, $fallback);

        $this->assertSame($expectedDetail === null ? $fallback : $fallback . ': ' . $expectedDetail, $message);
        $this->assertStringNotContainsString('secret', $message);
        $this->assertStringNotContainsString("\n", $message);
    }

    public function logMessages(): array
    {
        $request = new Request('POST', 'https://uniteller.test/pay?Password=secret', [], 'secret request body');
        $response = new Response(200, [], 'secret response body');
        $subclass = new class('Authentication error', $request, $response) extends AuthenticationException {};

        return [
            'fixed authentication error' => [
                new AuthenticationException('Authentication error', $request, $response), 'Authentication error',
            ],
            'fixed confirmation error' => [
                new AuthConfirmIsNotAllowedException('Authorization confirm is not allowed', $request, $response),
                'Authorization confirm is not allowed',
            ],
            'fixed operation error' => [
                new OperationFailedException('The operation failed', $request, $response), 'The operation failed',
            ],
            'fixed JSON error' => [new InvalidResponseException('Invalid JSON response'), 'Invalid JSON response'],
            'fixed XML error' => [new InvalidResponseException('Cannot decode XML response'), 'Cannot decode XML response'],
            'foreign exception with familiar text' => [new \RuntimeException('Authentication error'), null],
            'PHP error' => [new \TypeError('secret type mismatch'), null],
            'HTTP exception with request URI' => [RequestException::create($request, new Response(400)), null],
            'known class with dynamic text' => [
                new AuthenticationException('Authentication error: secret buyer@example.test', $request, $response), null,
            ],
            'known class with extra line' => [
                new AuthenticationException("Authentication error\nsecret", $request, $response), null,
            ],
            'field error contains provider data' => [
                new BadFieldFormatException('Field Email has bad format: secret', $request, $response), null,
            ],
            'unexpected SDK message' => [new InvalidResponseException('secret provider response'), null],
            'custom subclass' => [$subclass, null],
        ];
    }

    public function throwables(): array
    {
        $previous = new \LogicException('secret previous cause');
        $request = new Request('POST', 'https://uniteller.test/pay?Password=secret', [
            'Authorization' => 'Bearer secret-token',
        ], 'secret request body');
        $response = new Response(400, ['Set-Cookie' => 'session=secret'], 'secret response body');

        return [
            'runtime exception with previous cause' => [
                new \RuntimeException('secret buyer@example.test', 17, $previous),
                \RuntimeException::class,
                17,
            ],
            'PHP error' => [new \TypeError('secret type mismatch'), \TypeError::class, 0],
            'SDK exception with HTTP context' => [
                RequestException::create($request, $response, $previous),
                RequestException::class,
                400,
            ],
        ];
    }
}
