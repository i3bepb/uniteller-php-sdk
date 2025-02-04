<?php

namespace Tmconsulting\Uniteller\Payment;

use Tmconsulting\Uniteller\Common\EnumToArray;

final class CallbackFields
{
    use EnumToArray;

    /**
     * Идентификатор банка-эквайера в системе Uniteller.
     */
    const ACQUIRER_ID = 'AcquirerID';
    /**
     * Код подтверждения транзакции от процессингового центра.
     */
    const APPROVAL_CODE = 'ApprovalCode';
    /**
     * Номер платежа в системе Uniteller (RRN).
     */
    const BILL_NUMBER = 'BillNumber';
    /**
     * Идентификатор зарегистрированной карты (передаётся только для уже зарегистрированных в Uniteller карт).
     */
    const CARD_IDP = 'Card_IDP';
    /**
     * Маскированный номер карты (передается только для операций с картой).
     */
    const CARD_NUMBER = 'CardNumber';
    /**
     * Идентификатор Покупателя.
     */
    const CUSTOMER_IDP = 'Customer_IDP';
    /**
     * Информация о 3DS (передается только для операций с картой), возможные значения:
     *  5 — 3DS Full;
     *  6 — 3DS-Acquirer only;
     *  7 — E-commerce без 3DS.
     */
    const ECI = 'ECI';
    /**
     * Тип электронной валюты (передается только для операций с электронной валютой).
     */
    const E_MONEY_TYPE = 'EMoneyType';
    /**
     * Тип оплаты, возможные значения:
     *  1 — оплата картой;
     *  3 — оплата электронной валютой;
     *  13 — оплата через СБП;
     *  14 – оплата через SberPay.
     */
    const PAYMENT_TYPE = 'PaymentType';
    /**
     * Сумма всех средств, уплаченных по одному заказу.
     */
    const TOTAL = 'Total';
    /**
     * Текущий баланс платежа (с учётом частичных отмен и возвратов).
     */
    const BALANCE = 'Balance';
}
