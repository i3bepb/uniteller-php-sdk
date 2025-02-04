<?php

namespace Tmconsulting\Uniteller\Request;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

class ApiEndpoints
{
    use EnumToArrayTrait;

    /**
     * Оплата.
     */
    const PAYMENT = 'https://wpay.uniteller.ru/pay';

    /**
     * Изменение статуса регистрации карты.
     */
    const CARD = 'https://wpay.uniteller.ru/cardv3';

    /**
     * Подтверждение платежа, проведённого с преавторизацией.
     */
    const CONFIRM = 'https://wpay.uniteller.ru/confirm';

    /**
     * Рекуррентные платежи.
     */
    const RECURRENT = 'https://wpay.uniteller.ru/recurrent';

    /**
     * Отмена платежа без фискализации.
     * @deprecated
     */
    const CANCEL = 'https://wpay.uniteller.ru/unblock';

    /**
     * Запрос результата авторизации.
     */
    const RESULTS = 'https://wpay.uniteller.ru/results';

    /**
     * Оплата с фискализацией.
     */
    const FISCAL_PAYMENT = 'https://fpay.uniteller.ru/v2/pay';

    /**
     * Преавторизация с фискализацией и автоматической печатью чека аванса.
     */
    const FISCAL_PREAUTH_PAYMENT_WITH_ADVANCE_RECEIPT = 'https://fpay.uniteller.ru/v2/preauth';

    /**
     * Преавторизация с фискализацией без печати чека аванса.
     */
    const FISCAL_PREAUTH_PAYMENT = 'https://fpay.uniteller.ru/v2/preauth2';

    /**
     * Подтверждение платежа с преавторизацией и фискализацией.
     */
    const FISCAL_CONFIRM = 'https://fpay.uniteller.ru/v2/api/iaconfirm';

    /**
     * Отмена платежа с фискализацией.
     */
    const FISCAL_CANCEL = 'https://fpay.uniteller.ru/v2/cancel';

    /**
     * Запрос результата авторизации с данными фискализации.
     */
    const FISCAL_RESULTS = 'https://fpay.uniteller.ru/v2/results';
}
