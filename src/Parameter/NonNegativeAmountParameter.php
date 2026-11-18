<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Parameter\Traits\AmountValidation;

/**
 * Денежная сумма, допускающая ноль.
 *
 * Принимает int или строку с точкой и не более чем двумя знаками после неё.
 * Исходное представление суммы сохраняется для отправки и расчёта подписи.
 * Отрицательные значения и float не допускаются.
 */
class NonNegativeAmountParameter extends BaseParameter
{
    use AmountValidation;

    /**
     * Проверяет тип, формат и неотрицательность суммы.
     *
     * @param mixed $value Проверяемая денежная сумма.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        $this->assertValidNonNegativeAmount($this->unitellerName, $value);
    }
}
