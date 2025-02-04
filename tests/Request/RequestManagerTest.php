<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use I3bepb\ReflectionForTest\AccessToMethod;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Exception\RequestException;
use Tmconsulting\Uniteller\Exception\ServerErrorException;
use Tmconsulting\Uniteller\Exception\UnitellerException;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Request\ParserXml;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Request\RequestManager
 */
class RequestManagerTest extends TestCase
{
    use AccessToMethod;

    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    private $streamFactory;
    /**
     * @var \Psr\Http\Client\ClientInterface
     */
    private $httpClient;
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserInterface
     */
    private $parser;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Tmconsulting\Uniteller\Request\RequestManager
     */
    private $requestManager;

    protected function setUp(): void
    {
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->parser = $this->createMock(ParserXml::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->requestManager = new RequestManager(
            $this->requestFactory,
            $this->streamFactory,
            $this->httpClient,
            $this->parser
        );

        $this->requestManager->setLogger($this->logger);
        $this->requestManager->setOptions(['base_uri' => 'https://api.uniteller.test']);
    }

    public static function dataProviderGetDefaultHeaders(): array
    {
        return [
            ['xml', 'application/xml'],
            ['json', 'application/json'],
            ['csv', 'text/csv'],
        ];
    }

    /**
     * @param string $format
     * @param string $expected
     *
     * @dataProvider dataProviderGetDefaultHeaders
     *
     * @throws \ReflectionException
     */
    public function testGetDefaultHeaders(string $format, string $expected)
    {
        $xmlHeaders = $this->privateMethodWithParameters($this->requestManager, 'getDefaultHeaders', [$format]);
        $this->assertSame($expected, $xmlHeaders['Accept']);
        $this->assertSame('application/x-www-form-urlencoded', $xmlHeaders['Content-Type']);
    }

    public function testGetDefaultHeadersAnyFormat()
    {
        $xmlHeaders = $this->privateMethodWithParameters($this->requestManager, 'getDefaultHeaders', ['any']);
        $this->assertTrue(empty($xmlHeaders['Accept']));
        $this->assertCount(1, $xmlHeaders);
        $this->assertSame('application/x-www-form-urlencoded', $xmlHeaders['Content-Type']);
    }

    public static function dataProviderSanitizeData(): array
    {
        return [
            [
                [UnitellerParameterName::PASSWORD => 'password', 'any' => 'others'],
                [UnitellerParameterName::PASSWORD => '*****', 'any' => 'others']
            ],
            [
                ['password2' => 'password', 'any' => 'others'],
                ['password2' => 'password', 'any' => 'others']
            ],
            [null, null],
        ];
    }

    /**
     * @param array|null $data
     * @param array|null $expected
     *
     * @dataProvider dataProviderSanitizeData
     *
     * @throws \ReflectionException
     */
    public function testSanitizeData(?array $data, ?array $expected)
    {
        $sanitizeData = $this->privateMethodWithParameters($this->requestManager, 'sanitizeData', [$data]);
        $this->assertEquals($expected, $sanitizeData);
    }

    public function testPasswordSanitizationInLogs()
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaders')->willReturn(['any_header' => '123']);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                ' Request to Uniteller: https://api.uniteller.test/api_point',
                $this->callback(function ($context) {
                    return $context['parameters'][UnitellerParameterName::PASSWORD] === '*****';
                })
            );

        $this->privateMethodWithParameters($this->requestManager, 'logRequest', [$request, 'api_point', [
            UnitellerParameterName::PASSWORD => 'secret',
            'any'                            => 'others',
        ]]);
    }

    public function testSuccessfulRequest()
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->exactly(2))
            ->method('__toString')
            ->willReturn('response_body');

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($request);
        $request->expects($this->exactly(3))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                static $calls = 0;
                $calls++;

                if ($calls === 1) {
                    $this->assertEquals('Content-Type', $name);
                    $this->assertEquals('application/x-www-form-urlencoded', $value);
                } elseif ($calls === 2) {
                    $this->assertEquals('Accept', $name);
                    $this->assertEquals('application/xml', $value);
                } elseif ($calls === 3) {
                    $this->assertEquals('Content-Length', $name);
                }

                return $request;
            });


        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(200);
        $response->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn($stream);

        $data = ['param1' => 'value1', 'param2' => 'value2'];
        $query = http_build_query($data);
        $expectedUrl = "https://api.uniteller.test/test?$query";

        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', $expectedUrl)
            ->willReturn($request);

        $this->streamFactory->expects($this->once())
            ->method('createStream')
            ->with($query)
            ->willReturn($stream);

        $this->httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->parser->expects($this->once())
            ->method('parse')
            ->with('response_body')
            ->willReturn(['parsed' => 'data']);

        $this->parser->expects($this->once())
            ->method('parseErrors')
            ->with(['parsed' => 'data'], $request, $response);

        $this->logger->expects($this->exactly(2))
            ->method('info');

        $result = $this->requestManager->request('test', 'POST', $data);

        $this->assertEquals(['parsed' => 'data'], $result);
    }

    public function testLogResponse()
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('response_body');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn(['any' => '123']);
        $response->method('getBody')->willReturn($stream);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                ' Response from Uniteller',
                [
                    'statusCode' => 200,
                    'headers'    => ['any' => '123'],
                    'body'       => 'response_body',
                ]
            );

        $this->privateMethodWithParameters($this->requestManager, 'logResponse', [$response]);
    }

    public function testRequestWithServerError()
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getHeaders')->willReturn([]);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($this->createMock(StreamInterface::class));

        $this->httpClient->method('sendRequest')->willReturn($response);

        $this->expectException(UnitellerException::class);
        $this->expectExceptionMessage('[url]  [http method]  [status code] 500 [reason phrase] ');

        $this->requestManager->request('test');
    }

    public function testRequestWithClientError()
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($this->createMock(StreamInterface::class));

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $response->method('getStatusCode')->willReturn(400);
        $response->method('getHeaders')->willReturn([]);

        $this->httpClient->method('sendRequest')->willReturn($response);

        $this->expectException(UnitellerException::class);
        $this->expectExceptionMessage('[url]  [http method]  [status code] 400 [reason phrase] ');

        $this->requestManager->request('test');
    }

    public function testExecuteResponseOrders()
    {
        $builder = $this->createMock(ResultsBuilder::class);
        $builder->method('getRequestName')->willReturn('orders');
        $builder->method('toArray')->willReturn(['order_id' => 123]);
        $builder->method('getFormat')->willReturn('json');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($stream);

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn('response_body');

        $this->httpClient->method('sendRequest')->willReturn($response);

        $this->parser->method('parse')->willReturn(['orders' => []]);
        $this->parser->method('parseErrors');

        $order = new Order();
        $this->parser->method('parseOrders')->willReturn([$order]);

        $result = $this->requestManager->executeRequestAndParseResponseOrders($builder);

        $this->assertIsArray($result);
        $this->assertInstanceOf(Order::class, $result[0]);
    }

    public function testExecuteResponseReceipt()
    {
        $builder = $this->createMock(FiscalConfirmBuilder::class);
        $builder->method('getRequestName')->willReturn('receipt');
        $builder->method('toArray')->willReturn(['receipt_id' => 456]);
        $builder->method('getFormat')->willReturn('xml');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($stream);

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn('response_body');

        $this->httpClient->method('sendRequest')->willReturn($response);

        $this->parser->method('parse')->willReturn(['receipts' => []]);
        $this->parser->method('parseErrors');

        $order = new Order();
        $this->parser->method('parseResults')->willReturn([$order]);

        $result = $this->requestManager->executeRequestAndParseResponseReceipt($builder);

        $this->assertIsArray($result);
        $this->assertInstanceOf(Order::class, $result[0]);
    }

    public function testServerErrorThrowsException()
    {
        $request = $this->createMock(RequestInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(500);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($stream);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);
        $this->httpClient->method('sendRequest')->willReturn($response);

        $this->expectException(ServerErrorException::class);

        $this->requestManager->request('test');
    }

    public function testClientErrorThrowsException()
    {
        $request = $this->createMock(RequestInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(400);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($stream);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);
        $this->httpClient->method('sendRequest')->willReturn($response);

        $this->expectException(RequestException::class);

        $this->requestManager->request('test');
    }

    public function testLogging()
    {
        $request = $this->createMock(RequestInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('POST');
        $request->method('getHeaders')->willReturn([]);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->willReturn([]);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn('response_body');

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->streamFactory->method('createStream')->willReturn($stream);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);
        $this->httpClient->method('sendRequest')->willReturn($response);
        $this->parser->method('parse')->willReturn([]);

        // Verify logging
        $this->logger->expects($this->exactly(2))
            ->method('info');

        $this->requestManager->request('test', 'POST', ['param' => 'value']);
    }
}
