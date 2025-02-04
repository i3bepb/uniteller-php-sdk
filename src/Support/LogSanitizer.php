<?php

namespace Tmconsulting\Uniteller\Support;

use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;

/**
 * Подготавливает копию параметров для записи в лог.
 */
final class LogSanitizer
{
    /**
     * Маскирует пароль, не изменяя исходный массив.
     *
     * @param array $parameters
     *
     * @return array
     */
    public static function sanitize(array $parameters): array
    {
        if (array_key_exists(UnitellerParameterName::PASSWORD, $parameters)) {
            $parameters[UnitellerParameterName::PASSWORD] = '*****';
        }

        return $parameters;
    }
}
