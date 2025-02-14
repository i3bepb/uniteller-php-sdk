<?php

namespace Tmconsulting\Uniteller\Common;

final class MeanType
{
    use EnumToArray;

    const ANY = 0;
    const VISA = 1;
    const MASTERCARD = 2;
    const DINERS_CLUB = 3;
    const JCB = 4;
    const AMERICAN_EXPRESS = 5;
}
