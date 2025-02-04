<?php

namespace Tmconsulting\Uniteller\Request;

use Mtownsend\XmlToArray\XmlToArray;
use Tmconsulting\Uniteller\Exception\InvalidResponseException;

/**
 * Проверяет корректность XML и преобразует его в массив с исходным регистром имён полей.
 */
class ParserXml implements ParserInterface
{
    /**
     * @param string $response Тело XML-ответа.
     *
     * @return array Декодированные XML-элементы без внешнего корневого элемента.
     *
     * @throws InvalidResponseException Если ответ пуст, XML некорректен или преобразование не удалось.
     */
    public function parse(string $response): array
    {
        if (trim($response) === '') {
            throw new InvalidResponseException('Empty XML response');
        }

        $previous = libxml_use_internal_errors(true);
        try {
            // Конвертер сам не проверяет ошибки loadXML().
            $document = new \DOMDocument();
            if (!$document->loadXML($response, LIBXML_NONET)) {
                throw new InvalidResponseException('Invalid XML response');
            }

            return XmlToArray::convert($response);
        } catch (\Throwable $e) {
            throw new InvalidResponseException('Cannot decode XML response', 0, $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
