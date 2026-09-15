<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
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
    public function testExecuteRequestAndLogging(string $format, string $accept)
    {
        $endpoint = 'https://uniteller.test/api';
        $data = ['OrderID' => '1', 'Password' => 'secret', 'Comment' => 'order & payment'];
        $query = 'OrderID=1&Password=secret&Comment=order+%26+payment';
        $builder = $this->builder($endpoint, $data, $format);
        $logger = $this->createMock(LoggerInterface::class);
        $this->manager->setLogger($logger);
        $requestId = null;
        $stages = [];
        $logger->expects($this->exactly(2))->method('info')->willReturnCallback(
            function ($message, array $context) use (&$requestId, &$stages, $endpoint, $accept) {
                if ($requestId === null) {
                    $stages[] = 'request log';
                    $this->assertSame('Request to Uniteller: ' . $endpoint, $message);
                    $requestId = $context['request_id'];
                    $this->assertSame('POST', $context['method']);
                    $this->assertSame([$accept], $context['headers']['Accept']);
                    $this->assertSame('*****', $context['parameters']['Password']);
                    $this->assertSame('1', $context['parameters']['OrderID']);
                    $this->assertSame('order & payment', $context['parameters']['Comment']);
                } else {
                    $stages[] = 'response log';
                    $this->assertSame('Response from Uniteller', $message);
                    $this->assertSame($requestId, $context['request_id']);
                    $this->assertSame(200, $context['statusCode']);
                    $this->assertSame('body', $context['body']);
                }
            }
        );
        $response = new Response(200, [], 'body');
        $sentRequest = null;
        $this->client->expects($this->once())->method('sendRequest')->willReturnCallback(
            function ($request) use ($accept, $response, $endpoint, $query, &$sentRequest, &$stages) {
                $stages[] = 'send';
                $sentRequest = $request;
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame($endpoint . '?' . $query, (string)$request->getUri());
                $this->assertSame($query, $request->getUri()->getQuery());
                $this->assertSame($accept, $request->getHeaderLine('Accept'));
                $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
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
        $this->assertSame($sentRequest, $decoded->getRequest());
    }

    public function formats(): array
    {
        return [['csv', 'text/csv'], ['xml', 'application/xml'], ['json', 'application/json']];
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
