<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Тип документа
 */
class DocumentType
{
    use EnumToArrayTrait;

    /**
     * Приход.
     */
    const INCOME = 0;
    /**
     * Возврат прихода.
     */
    const REFUND = 1;
    /**
     * Коррекция прихода.
     */
    const CORRECT_INCOME = 2;
    /**
     * Коррекция возврата прихода.
     */
    const CORRECT_REFUND = 3;
}
