<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Тип коррекции.
 */
class CorrectionType
{
    use EnumToArrayTrait;

    /**
     * Самостоятельно.
     */
    const SELF = 0;
    /**
     * По предписанию.
     */
    const DIRECTIVE = 1;
}
