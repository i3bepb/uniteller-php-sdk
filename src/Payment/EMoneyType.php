<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Payment;

use Tmconsulting\Uniteller\Common\EnumToArray;

/**
 * Class EMoneyType
 *
 * @package Tmconsulting\Client\Payment
 */
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
