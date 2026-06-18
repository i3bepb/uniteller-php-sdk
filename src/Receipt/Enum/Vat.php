<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class Vat
{
    use EnumToArrayTrait;

    /**
     * Не облагается НДС.
     */
    const NO_VAT = -1;
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
     * Облагается НДС по ставке 22%.
     */
    const TWENTY_TWO = 22;
    /**
     * Облагается НДС по расчетной ставке 5/105.
     */
    const FIVE_CALCULATED = 105;
    /**
     * Облагается НДС по расчетной ставке 7/107.
     */
    const SEVEN_CALCULATED = 107;
    /**
     * Облагается НДС по расчетной ставке 10/110.
     */
    const TEN_CALCULATED = 110;
    /**
     * Облагается НДС по расчетной ставке 20/120.
     */
    const TWENTY_CALCULATED = 120;
    /**
     * Облагается НДС по расчетной ставке 22/122.
     */
    const TWENTY_TWO_CALCULATED = 122;
}
