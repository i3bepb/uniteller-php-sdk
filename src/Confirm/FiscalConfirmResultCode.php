<?php

namespace Tmconsulting\Uniteller\Confirm;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

final class FiscalConfirmResultCode
{
    use EnumToArrayTrait;

    /**
     * Успешная операция.
     */
    const SUCCESS = 0;

    /**
     * Field Subtotal_P has invalid format
     * Сумма подтверждения преавторизации должна быть больше нуля.
     */
    const INVALID_SUBTOTAL_FORMAT = 4;

    /**
     * Cashbox settings does not set for upoint
     * Выключена фискализация у точки продажи.
     */
    const CASHBOX_SETTINGS_NOT_SET = 5;

    /**
     * Field Receipt has invalid format
     * Неверный формат поля Receipt.
     */
    const INVALID_RECEIPT_FORMAT = 10;

    /**
     * Invalid ReceiptSignature
     * Неверное значение поля ReceiptSignature.
     */
    const INVALID_RECEIPT_SIGNATURE = 11;

    /**
     * Field Receipt has invalid payments
     * В поле Receipt в блоке payments сумма оплат не совпадает с суммой товарных позиций блока lines.
     */
    const INVALID_RECEIPT_PAYMENTS = 12;

    /**
     * Field agent in Receipt is disabled
     * Выключена настройка «Разрешить агентские платежи».
     */
    const AGENT_IN_RECEIPT_DISABLED = 13;

    /**
     * Wrong payment amount
     * Заказ требует закрытия: сумма оплат в запросе должна быть равна разнице между суммой заказа и балансом.
     */
    const WRONG_PAYMENT_AMOUNT = 14;

    /**
     * Order is closed
     * Заказ закрыт.
     */
    const ORDER_CLOSED = 15;

    /**
     * Amount of payments is more than Order ballance
     * Сумма оплаты и сумма ДПС в поле Receipt превышают разницу между суммой заказа и балансом заказа.
     */
    const PAYMENTS_EXCEED_ORDER_BALANCE = 16;

    /**
     * Partial payment is not allowed. Wrong payment type
     * Частичная оплата невозможна: используются запрещённые для частичной оплаты типы ДПС:
     * аванс, кредит или встречное требование.
     */
    const PARTIAL_PAYMENT_NOT_ALLOWED = 17;

    /**
     * Payment type not allowed
     * Нет активного договора на обслуживание переданного типа ДПС.
     */
    const PAYMENT_TYPE_NOT_ALLOWED = 19;

    /**
     * Fiscal receipt failure
     * Ошибка фискализации чека. Сработала «Защита от штрафов ФНС».
     */
    const FISCAL_RECEIPT_FAILURE = 53;

    /**
     * Fiscal receipt failure. Order not cancelled
     * Ошибка фискализации чека. «Защита от штрафов ФНС» не включена, заказ не отменён.
     */
    const FISCAL_RECEIPT_FAILURE_ORDER_NOT_CANCELLED = 54;
}
