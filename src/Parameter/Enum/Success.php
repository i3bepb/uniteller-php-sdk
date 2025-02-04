<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Какие операции включать в ответ.
 */
class Success
{
    use EnumToArrayTrait;

    /**
     * Неуспешные (со статусами Waiting, Not Authorized).
     */
    const FAILED = 0;
    /**
     * Успешные (со статусами Authorized, Paid, Canceled).
     */
    const SUCCESSFUL = 1;
    /**
     * Все
     */
    const ALL = 2;
}
