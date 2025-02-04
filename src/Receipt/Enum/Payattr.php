<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Признак способа расчета.
 */
class Payattr
{
    use EnumToArrayTrait;

    /**
     * Полная предварительная оплата до момента передачи предмета расчёта.
     * Предоплата 100%.
     */
    const PREPAYMENT = 1;
    /**
     * Частичная предварительная оплата до момента передачи предмета расчёта.
     * Частичная предоплата.
     */
    const PARTIAL_PREPAYMENT = 2;
    /**
     * Аванс.
     */
    const ADVANCE_PAYMENT = 3;
    /**
     * Полная оплата, в том числе с учётом аванса (предварительной оплаты) в момент передачи предмета расчёта.
     */
    const FULL_PAYMENT = 4;
    /**
     * Частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит.
     */
    const PARTIAL_PAYMENT_AND_CREDIT = 5;
    /**
     * Передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит.
     */
    const TRANSFER_ON_CREDIT = 6;
    /**
     * Оплата предмета расчёта после его передачи с оплатой в кредит (оплата кредита).
     */
    const CREDIT_PAYMENT = 7;
}
