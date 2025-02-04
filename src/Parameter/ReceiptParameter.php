<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Receipt\Receipt;

class ReceiptParameter extends BaseParameter
{
    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        if (!$value instanceof Receipt) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be instance of Receipt."
            );
        }
    }

    /**
     * Возвращает base64 представление чека.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value instanceof Receipt ? $this->value->toBase64() : null;
    }
}
