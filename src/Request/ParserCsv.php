<?php

namespace Tmconsulting\Uniteller\Request;

use ParseCsv\Csv;

/**
 * Декодирует CSV с автоматическим определением разделителя и заголовков.
 */
class ParserCsv implements ParserInterface
{
    /**
     * @param string $response Тело CSV-ответа.
     *
     * @return array Массив строк с именами столбцов в качестве ключей.
     */
    public function parse(string $response): array
    {
        $csv = new Csv();
        $csv->auto($response);

        return $csv->data;
    }
}
