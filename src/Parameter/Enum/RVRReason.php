<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Причина отмена операции.
 * По умолчанию 1.
 */
class RVRReason
{
    use EnumToArrayTrait;

    /**
     * Отказ магазина от операции
     */
    const SHOP = 1;

    /**
     * Отказ держателя от операции
     */
    const CARDHOLDER = 2;

    /**
     * Мошенническая операция
     */
    const FRAUD = 3;
}
