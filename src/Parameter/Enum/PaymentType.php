<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class PaymentType
{
    use EnumToArrayTrait;

    /**
     * Банковские карты
     */
    const BANK_CARD = '1';
    /**
     * Оплата с помощью электронной валюты
     */
    const E_MONEY = '3';
    /**
     * Оплата СБП
     */
    const SBP = '13';
    /**
     * Оплата через SberPay
     */
    const SBER_PAY = '14';
    /**
     * СПБ B2B
     */
    const SBP_B2B = '16';
}
