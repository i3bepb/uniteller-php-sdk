<?php

namespace Tmconsulting\Uniteller\Request;

/**
 * Декодирует тело ответа в массив без разбора заказов, чеков и ошибок операции.
 */
interface ParserInterface
{
    /**
     * @param string $response Тело HTTP-ответа в поддерживаемом парсером формате.
     *
     * @return array Данные с именами полей исходного ответа.
     *
     * @throws \Tmconsulting\Uniteller\Exception\InvalidResponseException При обнаружении некорректного ответа.
     */
    public function parse(string $response): array;
}
