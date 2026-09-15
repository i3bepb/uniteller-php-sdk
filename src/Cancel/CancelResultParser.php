<?php

namespace Tmconsulting\Uniteller\Cancel;

use Tmconsulting\Uniteller\Exception\ExceptionFactory;
use Tmconsulting\Uniteller\Request\DecodedResponse;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

/**
 * Разбирает результат отмены: декодирует чеки и проверяет код ошибки Uniteller.
 */
class CancelResultParser
{
    /** @var ParserReceiptFromBase64 Парсер фискальных чеков из поля Receipt. */
    private $parserReceipt;

    /**
     * @param ParserReceiptFromBase64 $parserReceipt Парсер чеков в кодировке Base64.
     */
    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    /**
     * @param DecodedResponse $response Декодированный JSON-ответ с исходными HTTP-сообщениями.
     *
     * @return array Поля ответа; непустое поле Receipt заменяется массивом FiscalReceipt.
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException Если непустой Code отличается от строки «00».
     * @throws \RuntimeException Если не удалось декодировать чеки.
     */
    public function parse(DecodedResponse $response): array
    {
        $data = $response->getData();
        if (!empty($data['Receipt'])) {
            $data['Receipt'] = $this->parserReceipt->parse($data['Receipt']);
        }
        if (!empty($data['Code']) && $data['Code'] !== '00') {
            throw ExceptionFactory::create(
                $data['Code'], $data['Note'], $response->getRequest(), $response->getResponse()
            );
        }

        return $data;
    }
}
