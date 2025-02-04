<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Traits\TrimmedStringValidation;

class PhoneParameter extends BaseParameter
{
    use TrimmedStringValidation;

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        if (!is_string($value)) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be string."
            );
        }
        $this->assertValidTrimmedString($value, $this->unitellerName);
        // Формат +7XXXXXXXXXX (12 символов)
        if (!preg_match('/^\+7\d{10}$/', $value)) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must match format +7XXXXXXXXXX."
            );
        }
    }
}
