<?php

namespace Tmconsulting\Uniteller\Parameter\Traits;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

trait AmountValidation
{
    /**
     * @param string $name Имя параметра в API Uniteller.
     * @param mixed $amount Проверяемая денежная сумма, которая должна быть строго больше нуля.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function assertValidAmount(string $name, $amount): void
    {
        $this->assertValidNonNegativeAmount($name, $amount);
        $strAmount = (string)$amount;
        if ($strAmount === '0' || $strAmount === '0.0' || $strAmount === '0.00') {
            throw new NotValidParameterException(
                "Invalid {$name}: amount must be greater than zero."
            );
        }
    }

    /**
     * Проверяет неотрицательную денежную сумму без преобразования в float.
     *
     * Допускает целое число или строку с точкой и не более чем двумя знаками после неё.
     * Ноль допустим, отрицательные значения запрещены.
     *
     * @param string $name Имя параметра в API Uniteller.
     * @param mixed $amount Проверяемая денежная сумма.
     *
     * @return void
     *
     * @throws NotValidParameterException Если тип или формат суммы недопустим.
     */
    protected function assertValidNonNegativeAmount(string $name, $amount): void
    {
        if (!is_string($amount) && !is_int($amount)) {
            throw new NotValidParameterException("Invalid {$name}: amount must be provided as string or integer.");
        }
        $strAmount = (string)$amount;
        if (!preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/D', $strAmount)) {
            throw new NotValidParameterException(
                "Invalid {$name}: amount must be a non-negative number with up to 2 decimal places."
            );
        }
    }
}
