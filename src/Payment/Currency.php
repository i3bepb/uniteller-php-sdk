<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Payment;

use Tmconsulting\Uniteller\Common\EnumToArray;

/**
 * Class Currency
 *
 * @package Tmconsulting\Client\Payment
 */
final class Currency
{
    use EnumToArray;

    /**
     * российский рубль
     */
    const RUB = 'RUB';

    /**
     * украинская гривна
     */
    const UAH = 'UAH';

    /**
     * азербайджанский манат
     */
    const AZN = 'AZN';

    /**
     * казахский тенге
     */
    const KZT = 'KZT';

    /**
     * евро
     */
    const EUR = 'EUR';

    /**
     * киргизский сом
     */
    const KGS = 'KGS';

    /**
     * доллар США
     */
    const USD = 'USD';
}
