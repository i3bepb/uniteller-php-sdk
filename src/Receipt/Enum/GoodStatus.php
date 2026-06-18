<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Планируемый статус товара, подлежащего обязательной маркировке
 * средством идентификации (тег 2003, goodstatus).
 */
class GoodStatus
{
    use EnumToArrayTrait;

    /**
     * Штучный товар реализован.
     */
    const PIECE_SOLD = 1;
    /**
     * Мерный товар находится в стадии реализации.
     */
    const MEASURED_IN_SALE_PROCESS = 2;
    /**
     * Штучный товар возвращён.
     */
    const PIECE_RETURNED = 3;
    /**
     * Часть товара возвращена.
     */
    const PART_RETURNED = 4;
    /**
     * Штучный товар находится в стадии реализации.
     */
    const PIECE_IN_SALE_PROCESS = 5;
    /**
     * Мерный товар реализован.
     */
    const MEASURED_SOLD = 6;
    /**
     * Статус товара не изменился.
     */
    const UNCHANGED = 255;
}
