<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class PaymentsResults
{
    use EnumToArrayTrait;

    /**
     * Только последняя оплата по заказу.
     */
    const LAST_PAYMENT = 0;
    /**
     * Только успешные попытки оплаты.
     */
    const SUCCESSFUL_PAYMENTS = 1;
    /**
     * Все попытки оплаты по заказу, включая неуспешные.
     */
    const ALL_PAYMENTS = 2;
    /**
     * Только последнюю оплату по заказу и успешные возвраты.
     */
    const LAST_PAYMENT_AND_SUCCESSFUL_RETURNS = 3;
}
