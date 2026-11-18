<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tmconsulting\Uniteller\Builder\BuilderInterface;
use Tmconsulting\Uniteller\Exception\RequestException;
use Tmconsulting\Uniteller\Exception\ServerErrorException;
use Tmconsulting\Uniteller\Request\DecodedResponse;
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
    public function testExecuteRequestAndLogging(string $format, string $accept, string $password)
    {
        $endpoint = 'https://uniteller.test/api';
        $data = ['OrderID' => '1', 'Password' => $password, 'Comment' => 'order & payment'];
        $query = 'OrderID=1&Password=' . $password . '&Comment=order+%26+payment';
        $builder = $this->builder($endpoint, $data, $format);
        $extraHeaders = [
            'Authorization' => 'Bearer request-token',
            'Cookie' => 'session=request-cookie',
            'X-Unlisted-Header' => 'request-private-value',
        ];
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->with('POST', $endpoint)
            ->willReturn(new Request('POST', $endpoint, $extraHeaders));
        $this->manager = new RequestManager($requestFactory, new HttpFactory(), $this->client, $this->parser);
        $logger = $this->createMock(LoggerInterface::class);
        $this->manager->setLogger($logger);
        $requestId = null;
        $stages = [];
        $logger->expects($this->exactly(2))->method('info')->willReturnCallback(
            function ($message, array $context) use (&$requestId, &$stages, $endpoint) {
                $this->assertSame(['uniteller'], array_keys($context));
                $context = $context['uniteller'];
                if ($requestId === null) {
                    $stages[] = 'request log';
                    $this->assertSame('uniteller.http.request_sending', $message);
                    $this->assertSame($endpoint, $context['url']);
                    $requestId = $context['request_id'];
                    $this->assertSame('POST', $context['method']);
                    $this->assertSame(['Content-Type' => 'application/x-www-form-urlencoded'], $context['headers']);
                    $this->assertSame('[REDACTED]', $context['parameters']['Password']);
                    $this->assertSame('1', $context['parameters']['OrderID']);
                    $this->assertSame('order & payment', $context['parameters']['Comment']);
                    $this->assertArrayNotHasKey('body', $context);
                } else {
                    $stages[] = 'response log';
                    $this->assertSame('uniteller.http.response_received', $message);
                    $this->assertSame($requestId, $context['request_id']);
                    $this->assertSame(200, $context['statusCode']);
                    $this->assertSame(['Content-Type' => 'text/plain'], $context['headers']);
                    $this->assertSame('[unparsed body: 4 bytes]', $context['body']);
                }
            }
        );
        $response = new Response(200, [
            'Content-Type' => 'text/plain',
            'Set-Cookie' => 'session=response-cookie',
            'X-Unlisted-Header' => 'response-private-value',
        ], 'body');
        $sentRequest = null;
        $this->client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function ($request) use ($accept, $response, $endpoint, $query, $extraHeaders, &$sentRequest, &$stages) {
                $stages[] = 'send';
                $sentRequest = $request;
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame($endpoint, (string)$request->getUri());
                $this->assertSame('', $request->getUri()->getQuery());
                $this->assertSame($accept, $request->getHeaderLine('Accept'));
                $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                foreach ($extraHeaders as $name => $value) {
                    $this->assertSame($value, $request->getHeaderLine($name));
                }
                $this->assertSame($query, (string)$request->getBody());
                return $response;
            }
        );
        $this->parser->expects($this->once())->method('parse')->with('body')->willReturnCallback(
            function ($body) use (&$stages) {
                $stages[] = 'decode';
                return ['Result' => '11'];
            }
        );
        $decoded = $this->manager->executeRequest($builder);
        $this->assertSame(['request log', 'send', 'response log', 'decode'], $stages);
        $this->assertInstanceOf(DecodedResponse::class, $decoded);
        $this->assertSame(['Result' => '11'], $decoded->getData());
        $this->assertSame($response, $decoded->getResponse());
        $this->assertSame('session=response-cookie', $decoded->getResponse()->getHeaderLine('Set-Cookie'));
        $this->assertSame('response-private-value', $decoded->getResponse()->getHeaderLine('X-Unlisted-Header'));
        $this->assertSame($sentRequest, $decoded->getRequest());
    }

    public function formats(): array
    {
        return [
            ['csv', 'text/csv', 'secret'],
            ['xml', 'application/xml', 'secret'],
            ['json', 'application/json', 'secret'],
            ['csv', 'text/csv', '0'],
            ['xml', 'application/xml', '0'],
            ['json', 'application/json', '0'],
        ];
    }

    /**
     * Декодирование сохраняет поля ошибки Uniteller для последующего разбора результата операции.
     */
    public function testExecuteRequestPreservesBusinessErrorData()
    {
        $data = ['ErrorCode' => '100', 'ErrorMessage' => 'Business error', 'Result' => '11'];
        $this->client->method('sendRequest')->willReturn(new Response(200, [], 'body'));
        $this->parser->expects($this->once())->method('parse')->with('body')->willReturn($data);
        $decoded = $this->manager->executeRequest($this->builder());
        $this->assertSame($data, $decoded->getData());
    }

    /** @dataProvider httpErrors */
    public function testHttpErrorsAreCheckedBeforeDecoding(int $status, string $exception)
    {
        $response = new Response($status, [], 'invalid body');
        $this->client->method('sendRequest')->willReturn($response);
        $this->parser->expects($this->never())->method('parse');
        $this->expectException($exception);
        $this->manager->executeRequest($this->builder());
    }

    public function httpErrors(): array
    {
        return [[400, RequestException::class], [404, RequestException::class], [500, ServerErrorException::class], [503, ServerErrorException::class]];
    }

    public function testClientErrorPropagates()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with(
            'uniteller.http.request_sending',
            $this->callback(function (array $context) {
                $this->assertSame(['uniteller'], array_keys($context));
                $this->assertSame('POST', $context['uniteller']['method']);
                return true;
            })
        );
        $this->manager->setLogger($logger);
        $exception = new ConnectException('Connection failed', new Request('POST', 'https://uniteller.test'));
        $this->client->method('sendRequest')->willThrowException($exception);
        $this->parser->expects($this->never())->method('parse');
        try {
            $this->manager->executeRequest($this->builder());
        } catch (ClientExceptionInterface $caught) {
            $this->assertSame($exception, $caught);
            return;
        }
        $this->fail('Исключение HTTP-клиента должно пробрасываться без изменений.');
    }

    /**
     * Создаёт билдер с адресом, параметрами и форматом ответа для проверки отправки запроса.
     *
     * @return BuilderInterface Мок источника данных запроса.
     */
    private function builder(string $endpoint = 'https://uniteller.test', array $data = [], string $format = 'xml'): BuilderInterface
    {
        $builder = $this->createMock(BuilderInterface::class);
        $builder->expects($this->once())->method('getEndpoint')->willReturn($endpoint);
        $builder->expects($this->once())->method('toArray')->willReturn($data);
        $builder->expects($this->once())->method('getResponseFormat')->willReturn($format);
        return $builder;
    }
}
