<?php

namespace Tmconsulting\Uniteller\Common;

final class EMoneyType
{
    use EnumToArray;

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
     * QIWI Кошелек REST (по протоколу REST)
     */
    const QIWI_REST = 18;

    /**
     * MOBI.Деньги
     */
    const MOBI = 19;

    /**
     * WebMoney WMR
     */
    const WEBMONEY_WMR = 29;
}
