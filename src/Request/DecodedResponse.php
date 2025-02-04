<?php

namespace Tmconsulting\Uniteller\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Декодированное тело ответа и исходные HTTP-сообщения.
 *
 * Парсеры результатов используют HTTP-сообщения при создании исключений Uniteller.
 */
class DecodedResponse
{
    /** @var array Данные в структуре выбранного формата ответа. */
    private $data;
    /** @var RequestInterface Отправленный HTTP-запрос. */
    private $request;
    /** @var ResponseInterface Полученный HTTP-ответ. */
    private $response;

    /**
     * @param array $data Декодированное тело ответа до разбора результата операции.
     * @param RequestInterface $request Отправленный HTTP-запрос.
     * @param ResponseInterface $response Полученный HTTP-ответ.
     */
    public function __construct(array $data, RequestInterface $request, ResponseInterface $response)
    {
        $this->data = $data;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return array Декодированное тело ответа.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return RequestInterface Исходный запрос для диагностики ошибок операции.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface Исходный ответ для диагностики ошибок операции.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
