<?php

namespace Tmconsulting\Uniteller\Receipt\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Тип дополнительного платежного средства.
 */
class TypePaymentMethod
{
    use EnumToArrayTrait;

    /**
     * Дополнительное платежное средство не используется.
     */
    const WITHOUT_ADDITIONAL = 0;
    /**
     * Подарочные карты Мерчанта.
     */
    const GIFT_CARD = 1;
    /**
     * Бонусы-авансы Мерчанта.
     */
    const BONUS = 2;
    /**
     * Прямой аванс Мерчанта.
     */
    const ADVANCE = 3;
    /**
     * Использование авансов/билетов.
     */
    const USE_TICKET = 4;
    /**
     * Платеж через кредитную организацию (банкомат).
     */
    const AUTOMATED_TELLER_MACHINE = 5;
    /**
     * Платеж через кредитную организацию (online).
     */
    const ONLINE = 6;
    /**
     * Безналичное перечисление через банк.
     */
    const BANK = 7;
    /**
     * Оплата «онлайн кредитом».
     */
    const ONLINE_CREDIT = 8;
    /**
     * Оплата по СМС.
     */
    const SMS = 9;
    /**
     * Эквайринг внешний.
     */
    const EXTERNAL_ACQUIRING = 10;
    /**
     * Платеж через терминал электронными.
     */
    const ELECTRONIC_TERMINAL = 11;
    /**
     * Платеж через терминал наличными.
     */
    const CASH_TERMINAL = 12;
    /**
     * Наличные.
     */
    const CASH = 13;
    /**
     * Продажа в кредит.
     */
    const CREDIT_SALE = 14;
    /**
     * Встречное предоставление.
     */
    const COUNTER = 15;
    /**
     * Сертификат Uniteller.
     */
    const GIFT_UNITELLER = 16;
}
