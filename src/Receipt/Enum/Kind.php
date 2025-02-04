<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Вид платежного средства.
 */
class Kind
{
    use EnumToArrayTrait;

    /**
     * Оплата банковской картой.
     */
    const CARD = 1;
    /**
     * Оплата электронной валютой.
     */
    const ELECTRONIC = 2;
    /**
     * Оплата с помощью кредитной организации.
     */
    const CREDIT = 3;
    /**
     * Оплата дополнительным платежным средством.
     */
    const ADDITIONAL = 4;
}
