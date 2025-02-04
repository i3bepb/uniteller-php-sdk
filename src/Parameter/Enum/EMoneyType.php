<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Тип электронной валюты.
 */
class EMoneyType
{
    use EnumToArrayTrait;

    /**
     * Любая система электронных платежей
     */
    const ANY = 0;

    /**
     * Яндекс.Деньги
     */
    const YANDEX_MONEY = 1;

    /**
     * Оплата наличными (Евросеть, Яндекс.Деньги и пр.)
     */
    const CASH = 13;

    /**
     * QIWI Кошелек (REST)
     */
    const QIWI_REST = 18;

    /**
     * MOBI.Деньги
     */
    const MOBI_MONEY = 19;

    /**
     * WebMoney WMR
     */
    const WEBMONEY_WMR = 29;
}
