<?php

namespace Tmconsulting\Uniteller\Request;

use Tmconsulting\Uniteller\Exception\InvalidResponseException;

/**
 * Декодирует JSON-ответ, сохраняя имена полей и вложенную структуру.
 */
class ParserJson implements ParserInterface
{
    /**
     * @param string $response Тело JSON-ответа.
     *
     * @return array Декодированный JSON-объект или массив.
     *
     * @throws InvalidResponseException Если JSON некорректен или содержит скалярное значение либо null.
     */
    public function parse(string $response): array
    {
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new InvalidResponseException('Invalid JSON response');
        }

        return $data;
    }
}
