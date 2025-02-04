<?php

namespace Tmconsulting\Uniteller\Parameter\Traits;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

trait MaxLengthValidation
{
    /**
     * @param string $name
     * @param string $value
     * @param int|null $max
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function assertMaxLength(string $name, string $value, ?int $max): void
    {
        if ($max !== null) {
            if (mb_strlen($value) > $max) {
                throw new NotValidParameterException(
                    "Invalid {$name}: length must be less than or equal to {$max} characters."
                );
            }
        }
    }
}
