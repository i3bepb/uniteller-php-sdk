<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

final class CorrectionSubject
{
    use EnumToArrayTrait;

    /**
     * Коррекция прихода.
     */
    const INCOME = 0;

    /**
     * Коррекция возврата прихода.
     */
    const REFUND = 1;
}
