<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Common\EnumToArray;

final class Success
{
    use EnumToArray;

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
