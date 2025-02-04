<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Режим выдачи результата.
 */
class ZipFlag
{
    use EnumToArrayTrait;

    /**
     * Браузер.
     */
    const DEFAULT = 0;
    /**
     * Файл.
     */
    const FILE = 1;
    /**
     * Архивированный файл.
     */
    const ZIP = 2;
}
