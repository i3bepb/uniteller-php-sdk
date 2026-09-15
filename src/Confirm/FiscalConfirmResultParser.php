<?php

namespace Tmconsulting\Uniteller\Confirm;

use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

/**
 * Проверяет структуру ответа фискального подтверждения и создаёт результат с чеками.
 */
class FiscalConfirmResultParser
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
     * Разбирает поля Result, ErrorMessage и Receipt с учётом регистра имён.
     *
     * Result обязателен и должен содержать неотрицательное целое число либо его строковую запись.
     * Код результата, включая неизвестный, сохраняется в объекте и не превращается в исключение.
     * Отсутствующий ErrorMessage становится null, пустой XML-элемент — пустой строкой.
     * При отсутствии Receipt возвращается пустой массив чеков.
     *
     * @param array $data Декодированное тело XML-ответа фискального подтверждения.
     *
     * @return FiscalConfirmResult Код результата, сообщение и фискальные чеки.
     *
     * @throws InvalidResponseException Если обязательное поле отсутствует или типы полей некорректны.
     * @throws \RuntimeException Если не удалось декодировать содержимое Receipt.
     */
    public function parse(array $data): FiscalConfirmResult
    {
        if (!array_key_exists('Result', $data)) {
            throw new InvalidResponseException('Missing Result in FiscalConfirm response');
        }
        $result = $data['Result'];
        if ((!is_int($result) && !is_string($result))
            || !preg_match('/^[0-9]+$/D', (string)$result)
            || filter_var($result, FILTER_VALIDATE_INT) === false
        ) {
            throw new InvalidResponseException('Invalid Result in FiscalConfirm response');
        }
        $errorMessage = null;
        if (array_key_exists('ErrorMessage', $data)) {
            // Пустой XML-элемент декодируется как пустой массив.
            if ($data['ErrorMessage'] === []) {
                $errorMessage = '';
            } elseif (is_string($data['ErrorMessage'])) {
                $errorMessage = $data['ErrorMessage'];
            } else {
                throw new InvalidResponseException('Invalid ErrorMessage in FiscalConfirm response');
            }
        }
        $receipts = [];
        if (array_key_exists('Receipt', $data)) {
            if (!is_string($data['Receipt'])) {
                throw new InvalidResponseException('Invalid Receipt in FiscalConfirm response');
            }
            $receipts = $this->parserReceipt->parse($data['Receipt']);
        }

        return new FiscalConfirmResult((int)$result, $errorMessage, $receipts);
    }
}
