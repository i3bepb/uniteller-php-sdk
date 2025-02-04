<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Валюта
 */
class Currency
{
    use EnumToArrayTrait;

    /**
     * Российский рубль
     */
    const RUB = 'RUB';

    /**
     * Украинская гривна
     */
    const UAH = 'UAH';

    /**
     * Азербайджанский манат
     */
    const AZN = 'AZN';

    /**
     * Казахский тенге
     */
    const KZT = 'KZT';

    /**
     * Евро
     */
    const EUR = 'EUR';

    /**
     * Киргизский сом
     */
    const KGS = 'KGS';

    /**
     * Доллар США
     */
    const USD = 'USD';
}
