<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tmconsulting\Uniteller\Exception\RequestException;
use Tmconsulting\Uniteller\Exception\ServerErrorException;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Tests\TestCase;

class RequestManagerTest extends TestCase
{
    private $client;
    private $parser;
    private $manager;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->parser = $this->createMock(ParserInterface::class);
        $this->manager = new RequestManager(new HttpFactory(), new HttpFactory(), $this->client, $this->parser);
        $this->manager->setLogger(new NullLogger());
    }

    /** @dataProvider formats */
    public function testRequestAndLogging(string $format, string $accept)
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->manager->setLogger($logger);
        $requestId = null;
        $logger->expects($this->exactly(2))->method('info')->willReturnCallback(
            function ($message, array $context) use (&$requestId) {
                if ($requestId === null) {
                    $requestId = $context['request_id'];
                    $this->assertSame('*****', $context['parameters']['Password']);
                    $this->assertSame('1', $context['parameters']['OrderID']);
                } else {
                    $this->assertSame($requestId, $context['request_id']);
                    $this->assertSame(200, $context['statusCode']);
                    $this->assertSame('body', $context['body']);
                }
            }
        );
        $response = new Response(200, [], 'body');
        $this->client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function ($request) use ($accept, $response) {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame($accept, $request->getHeaderLine('Accept'));
                $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                $this->assertSame('test', $request->getHeaderLine('X-Test'));
                $this->assertSame('OrderID=1&Password=secret', (string)$request->getBody());
                return $response;
            }
        );
        $this->parser->expects($this->once())->method('parse')->with('body')->willReturn(['Result' => '11']);
        $decoded = $this->manager->requestDecoded(
            'https://uniteller.test/api', 'POST', ['OrderID' => '1', 'Password' => 'secret'], ['X-Test' => 'test'], $format
        );
        $this->assertSame(['Result' => '11'], $decoded->getData());
        $this->assertSame($response, $decoded->getResponse());
        $this->assertSame('uniteller.test', $decoded->getRequest()->getUri()->getHost());
    }

    public function formats(): array
    {
        return [['csv', 'text/csv'], ['xml', 'application/xml'], ['json', 'application/json']];
    }

    public function testRequestReturnsOnlyDecodedArray()
    {
        $this->client->method('sendRequest')->willReturn(new Response(200, [], 'body'));
        $this->parser->method('parse')->willReturn(['ErrorMessage' => 'Business error']);
        $this->assertSame(['ErrorMessage' => 'Business error'], $this->manager->request('https://uniteller.test'));
    }

    /** @dataProvider httpErrors */
    public function testHttpErrorsAreCheckedBeforeDecoding(int $status, string $exception)
    {
        $response = new Response($status, [], 'invalid body');
        $this->client->method('sendRequest')->willReturn($response);
        $this->parser->expects($this->never())->method('parse');
        $this->expectException($exception);
        $this->manager->request('https://uniteller.test');
    }

    public function httpErrors(): array
    {
        return [[400, RequestException::class], [404, RequestException::class], [500, ServerErrorException::class], [503, ServerErrorException::class]];
    }

    public function testClientErrorPropagates()
    {
        $exception = new ConnectException('Connection failed', new Request('POST', 'https://uniteller.test'));
        $this->client->method('sendRequest')->willThrowException($exception);
        $this->parser->expects($this->never())->method('parse');
        $this->expectExceptionObject($exception);
        $this->manager->request('https://uniteller.test');
    }
}
