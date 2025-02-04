<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class Taxmode
{
    use EnumToArrayTrait;

    /**
     * Общая система налогообложения.
     */
    const STANDARD = 0;
    /**
     * Упрощённая система налогообложения (Доход).
     */
    const SIMPLIFIED_INCOME = 1;
    /**
     * Упрощённая система налогообложения (Доход минус Расход).
     */
    const SIMPLIFIED_INCOME_MINUS_EXPENSES = 2;
    /**
     * Единый налог на вмененный доход.
     */
    const IMPUTED_INCOME_TAX = 3;
    /**
     * Единый сельскохозяйственный налог.
     */
    const AGRICULTURAL = 4;
    /**
     * Патентная система налогообложения
     */
    const PATENT = 5;
}
