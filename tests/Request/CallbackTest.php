<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use I3bepb\ReflectionForTest\AccessToMethod;
use Psr\Log\NullLogger;
use Tmconsulting\Uniteller\Callback\Callback;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Callback\Callback
 */
class CallbackTest extends TestCase
{
    use AccessToMethod;

    /**
     * @var \Tmconsulting\Uniteller\Callback\Callback
     */
    private $callback;

    protected function setUp(): void
    {
        $signatureMock = $this->createMock(SignatureInterface::class);
        $signatureMock->method('setFields')->willReturnSelf();
        $signatureMock->method('verify')->willReturn(false);

        $this->callback = new Callback($signatureMock);
        $this->callback->setLogger(new NullLogger());
    }

    public function testSetAndGetPassword()
    {
        $this->callback->setPassword('secret');
        $this->assertEquals('secret', $this->callback->getPassword());
    }

    public function testGetPasswordThrowsException()
    {
        $this->expectException(RequiredParameterException::class);
        $this->callback->getPassword();
    }

    public function testProcessWithMissingPostParams()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not found parameters');

        $this->callback->process();
    }

    public function testProcessWithInvalidSignature()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/callback';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
        $_POST = [
            'Order_ID'  => '12345',
            'Status'    => 'Paid',
            'Signature' => 'invalid_signature',
        ];

        $this->callback->setPassword('secret');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Signature not valid');

        $this->callback->process();
    }

    public function testExitWithMessage()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test error');
        $this->privateMethodWithParameters($this->callback, 'exitWithMessage', ['Test error', 400]);
    }
}
