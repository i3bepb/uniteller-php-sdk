<?php

namespace Tmconsulting\Uniteller\Parameter\Traits;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

trait TrimmedStringValidation
{
    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function assertValidTrimmedString(string $name, string $value): void
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new NotValidParameterException(
                "Invalid {$name}: must not be empty."
            );
        }
        if ($value !== $trimmed) {
            throw new NotValidParameterException(
                "Invalid {$name}: must not contain leading or trailing spaces."
            );
        }
    }
}
