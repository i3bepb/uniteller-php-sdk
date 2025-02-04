<?php

namespace Tmconsulting\Uniteller\Request;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Builder\BuilderInterface;
use Tmconsulting\Uniteller\Exception\RequestException;
use Tmconsulting\Uniteller\Exception\ServerErrorException;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;

class RequestManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var \Psr\Http\Client\ClientInterface
     */
    protected $httpClient;

    /**
     * @var \Tmconsulting\Uniteller\Request\ParserInterface
     */
    protected $parserOrders;

    /**
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
     * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
     * @param \Psr\Http\Client\ClientInterface $httpClient
     * @param \Tmconsulting\Uniteller\Request\ParserInterface $parser
     */
    public function __construct(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        ClientInterface $httpClient,
        ParserInterface $parser
    )
    {
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->httpClient = $httpClient;
        $this->parserOrders = $parser;
    }

    /**
     * @param string $responseFormat Формат ответа
     *
     * @return string[]
     */
    private function getDefaultHeaders(string $responseFormat): array
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        switch ($responseFormat) {
            case Format::XML:
                $headers['Accept'] = 'application/xml';
                break;
            case Format::CSV:
                $headers['Accept'] = 'text/csv';
                break;
            case Format::JSON:
                $headers['Accept'] = 'application/json';
                break;
        }

        return $headers;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array|null $data
     * @param array $headers
     * @param string $responseFormat Формат ответа.
     *
     * @return array
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Random\RandomException
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException
     * @throws \Tmconsulting\Uniteller\Exception\UnitellerException
     */
    public function request(string $url, string $method = 'POST', ?array $data = null, array $headers = [], string $responseFormat = 'xml'): array
    {
        $requestId = $this->generateRequestId();

        $query = $data !== null ? http_build_query($data) : '';
        $fullUrl = sprintf('%s?%s', $url, $query);

        $request = $this->requestFactory->createRequest($method, $fullUrl)
            ->withBody($this->streamFactory->createStream($query));
        foreach (array_merge($this->getDefaultHeaders($responseFormat), $headers) as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $this->logRequest($requestId, $request, $url, $data);
        $response = $this->httpClient->sendRequest($request);
        $this->logResponse($requestId, $response);

        $this->validateResponse($request, $response);

        return $this->parseResponse($response, $request);
    }

    /**
     * @param string $requestId
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string $uri
     * @param array|null $data
     */
    private function logRequest(string $requestId, RequestInterface $request, string $uri, ?array $data)
    {
        $this->logger->info('Request to Uniteller: ' . $uri, [
            'request_id' => $requestId,
            'method'     => $request->getMethod(),
            'headers'    => $request->getHeaders(),
            'parameters' => $data ? $this->sanitizeData($data) : null,
        ]);
    }

    /**
     * @param string $requestId
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function logResponse(string $requestId, ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        $response->getBody()->rewind();

        $this->logger->info('Response from Uniteller', [
            'request_id' => $requestId,
            'statusCode' => $response->getStatusCode(),
            'headers'    => $response->getHeaders(),
            'body'       => $body,
        ]);
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @throws \Tmconsulting\Uniteller\Exception\UnitellerException
     */
    protected function validateResponse(RequestInterface $request, ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            throw ServerErrorException::create($request, $response);
        }
        if ($statusCode >= 400 && $statusCode < 500) {
            throw RequestException::create($request, $response);
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException
     */
    protected function parseResponse(ResponseInterface $response, RequestInterface $request): array
    {
        $body = (string)$response->getBody();
        $data = $this->parserOrders->parse($body);
        $this->parserOrders->parseErrors($data, $request, $response);

        return $data;
    }

    /**
     * Маскирует чувствительные данные (например, пароль) перед логированием.
     *
     * @param array $data
     *
     * @return array
     */
    protected function sanitizeData(array $data): array
    {
        if (!empty($data[UnitellerParameterName::PASSWORD])) {
            $data[UnitellerParameterName::PASSWORD] = '*****';
        }
        return $data;
    }

    /**
     * Выполнение запроса, когда в ответе возвращаются заказы.
     *
     * @param \Tmconsulting\Uniteller\Builder\BuilderInterface $builder
     *
     * @return \Tmconsulting\Uniteller\Order\Order[]
     *
     * @throws \Throwable
     */
    public function executeRequestAndParseResponseOrders(BuilderInterface $builder)
    {
        $response = $this->request($builder->getEndpoint(), 'POST', $builder->toArray(), [], $builder->getResponseFormat());

        return $this->parserOrders->parseOrders($response);
    }

    /**
     * Выполнение запроса, когда в ответе возвращаются чеки. Запрос отмены оплаты с чеком.
     *
     * @param \Tmconsulting\Uniteller\Builder\BuilderInterface $builder
     *
     * @return \Tmconsulting\Uniteller\Order\Order[]
     *
     * @throws \Throwable
     */
    public function executeRequestAndParseResponseReceipt(BuilderInterface $builder)
    {
        $response = $this->request($builder->getEndpoint(), 'POST', $builder->toArray(), [], $builder->getResponseFormat());

        return $this->parserOrders->parseResults($response);
    }

    /**
     * @return string
     *
     * @throws \Random\RandomException
     */
    private function generateRequestId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
