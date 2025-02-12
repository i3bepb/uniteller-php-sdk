<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Common\EnumToArray;

final class PaymentsResults
{
    use EnumToArray;

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
