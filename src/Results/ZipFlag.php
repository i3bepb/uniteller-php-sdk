<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Common\EnumToArray;

final class ZipFlag
{
    use EnumToArray;

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
