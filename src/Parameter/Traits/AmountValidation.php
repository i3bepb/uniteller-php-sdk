<?php

namespace Tmconsulting\Uniteller\Parameter\Traits;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

trait AmountValidation
{
    /**
     * @param string $name
     * @param mixed $amount Денежная сумма
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function assertValidAmount(string $name, $amount): void
    {
        if (!is_string($amount) && !is_int($amount)) {
            throw new NotValidParameterException(
                "Invalid {$name}: amount must be provided as string or integer."
            );
        }
        $strAmount = (string)$amount;
        if (!preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/', $strAmount)) {
            throw new NotValidParameterException(
                "Invalid {$name}: amount must be a positive number with up to 2 decimal places."
            );
        }
        if ($strAmount === '0' || $strAmount === '0.0' || $strAmount === '0.00') {
            throw new NotValidParameterException(
                "Invalid {$name}: amount must be greater than zero."
            );
        }
    }
}
