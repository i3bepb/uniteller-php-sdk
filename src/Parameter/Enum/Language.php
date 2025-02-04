<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

final class Language
{
    use EnumToArrayTrait;

    /**
     * Русский язык.
     */
    const RU = 'ru';
    /**
     * Английский язык
     */
    const EN = 'en';
}
