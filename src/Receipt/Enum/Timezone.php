<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class Timezone
{
    use EnumToArrayTrait;

    /**
     * Калининградское время UTC+2
     */
    const KALININGRAD = 1;
    /**
     * Московское время UTC+3
     */
    const MOSCOW = 2;
    /**
     * UTC+4
     */
    const SAMARA = 3;
    /**
     *  UTC+5
     */
    const YEKATERINBURG = 4;
    /**
     * UTC+6
     */
    const OMSK = 5;
    /**
     * UTC+7
     */
    const KRASNOYARSK = 6;
    /**
     * UTC+8
     */
    const IRKUTSK = 7;
    /**
     * UTC+9
     */
    const YAKUTSK = 8;
    /**
     * UTC+10
     */
    const VLADIVOSTOK = 9;
    /**
     * UTC+11
     */
    const MAGADAN = 10;
    /**
     * UTC+12
     */
    const KAMCHATKA = 11;
}
