<?php

namespace Tmconsulting\Uniteller\Payment;

use Tmconsulting\Uniteller\Common\EnumToArray;

final class PaymentType
{
    use EnumToArray;

    /**
     * Банковские карты
     */
    const BANK_CARD = '1';
    /**
     * Оплата СБП
     */
    const FAST_PAYMENT_SYSTEM = '13';
    /**
     * Оплата через SberPay
     */
    const SBER_PAY = '14';
}
