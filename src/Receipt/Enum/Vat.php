<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class Vat
{
    use EnumToArrayTrait;

    /**
     * Не облагается НДС.
     */
    const FREE = -1;
    /**
     * Облагается НДС по ставке 0%.
     */
    const ZERO = 0;
    /**
     * Облагается НДС по ставке 5%.
     */
    const FIVE = 5;
    /**
     * Облагается НДС по ставке 7%.
     */
    const SEVEN = 7;
    /**
     * Облагается НДС по ставке 10%.
     */
    const TEN = 10;
    /**
     * Облагается НДС по ставке 20%.
     */
    const TWENTY = 20;
    /**
     * Облагается НДС по ставке 5/105.
     */
    const FIVE_INCLUDING = 105;
    /**
     * Облагается НДС по ставке 7/107.
     */
    const SEVEN_INCLUDING = 107;
    /**
     * Облагается НДС по ставке 10/110.
     */
    const TEN_INCLUDING = 110;
    /**
     * Облагается НДС по ставке 20/120.
     */
    const TWENTY_INCLUDING = 120;
}
