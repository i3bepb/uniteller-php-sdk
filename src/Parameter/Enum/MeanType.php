<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Платёжная система кредитной карты.
 */
class MeanType
{
    use EnumToArrayTrait;

    /**
     * Любая
     */
    const ANY_CARD = 0;
    /**
     * VISA
     */
    const VISA = 1;
    /**
     * MasterCard
     */
    const MASTERCARD = 2;
    /**
     * Diners Club
     */
    const DINERS_CLUB = 3;
    /**
     * JCB
     */
    const JCB = 4;
    /**
     * American Express
     */
    const AMERICAN_EXPRESS = 5;
}
