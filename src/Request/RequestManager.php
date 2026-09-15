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

/**
 * Отправляет HTTP-запросы, проверяет HTTP-статус и декодирует тело ответа.
 *
 * Разбор результата операции выполняется отдельным парсером на стороне билдера.
 */
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
    protected $parser;

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
        $this->parser = $parser;
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
     * Отправляет запрос и сохраняет HTTP-сообщения вместе с декодированным телом ответа.
     *
     * Проверяет HTTP-статус; поля ошибок Uniteller обрабатываются парсером результата операции.
     * Формат заголовка Accept должен соответствовать парсеру, переданному в конструктор.
     *
     * @param string $url Адрес метода API.
     * @param string $method HTTP-метод.
     * @param array|null $data Параметры для строки запроса и тела в формате application/x-www-form-urlencoded.
     * @param array $headers HTTP-заголовки, дополняющие или заменяющие заголовки по умолчанию.
     * @param string $responseFormat Формат ответа для заголовка Accept.
     *
     * @return DecodedResponse Декодированные данные, отправленный запрос и полученный ответ.
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface При ошибке HTTP-клиента.
     * @throws \Random\RandomException Если не удалось создать идентификатор запроса.
     * @throws RequestException При HTTP-статусе 4xx.
     * @throws ServerErrorException При HTTP-статусе 500 и выше.
     * @throws \Tmconsulting\Uniteller\Exception\InvalidResponseException При ошибке декодирования ответа.
     */
    public function requestDecoded(string $url, string $method = 'POST', ?array $data = null, array $headers = [], string $responseFormat = 'xml'): DecodedResponse
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

        return new DecodedResponse($this->parser->parse((string)$response->getBody()), $request, $response);
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
     * Отправляет POST-запрос с параметрами билдера и декодирует тело ответа.
     *
     * @param BuilderInterface $builder Обертка над параметрами запроса.
     *
     * @return DecodedResponse Декодированные данные с исходными HTTP-сообщениями.
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface При ошибке HTTP-клиента.
     * @throws \Random\RandomException Если не удалось создать идентификатор запроса.
     * @throws \Tmconsulting\Uniteller\Exception\UnitellerException При ошибке HTTP-статуса или декодирования.
     *
     * @see requestDecoded()
     */
    public function executeRequest(BuilderInterface $builder): DecodedResponse
    {
        return $this->requestDecoded(
            $builder->getEndpoint(), 'POST', $builder->toArray(), [], $builder->getResponseFormat()
        );
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
