<?php

namespace Tmconsulting\Uniteller\Results;

use Tmconsulting\Uniteller\Common\EnumToArray;

/**
 * Параметр S_FIELDS определяет набор информационных полей, возвращаемых в ответе на запрос.
 */
final class Fields
{
    use EnumToArray;

    /**
     * Адрес Держателя карты.
     */
    const ADDRESS = 'Address';
    /**
     * Код подтверждения транзакции от процессингово центра.
     */
    const APPROVAL_CODE = 'ApprovalCode';
    /**
     * Имя банка-эмитента.
     */
    const BANK_NAME = 'BankName';
    /**
     * Номер платежа в системе Uniteller.
     */
    const BILL_NUMBER = 'BillNumber';
    /**
     * Идентификатор (логин) Мерчанта в сервисе Booking.com.
     */
    const BOOKINGCOM_ID = 'bookingcom_id';
    /**
     * Пароль Мерчанта в сервисе Booking.com.
     */
    const BOOKINGCOM_PINCODE = 'bookingcom_pincode';
    /**
     * Идентификатор зарегистрированной карты.
     */
    const CARD_IDP = 'Card_IDP';
    /**
     * Информация, введённая Покупателем на странице оплаты в поле имени владельца карты.
     */
    const CARD_HOLDER = 'CardHolder';
    /**
     * Первые 6 цифр и последние 4 цифры номера карты (PAN), соединённые звёздочками.
     */
    const CARD_NUMBER = 'CardNumber';
    /**
     * Тип платёжной системы карты (возможные значения: visa, mastercard, dinnersclub, jcb, mir).
     */
    const CARD_TYPE = 'CardType';
    /**
     * Комментарий к оплате (передаётся в запросе на оплату).
     */
    const COMMENT = 'Comment';
    /**
     * Код валюты.
     */
    const CURRENCY = 'Currency';
    /**
     * Наличие CVC2/CVV2/4DBC.
     * (0 — авторизация без CVC2, 1 — авторизация с СVC2).
     */
    const CVC = 'CVC2';
    /**
     * Дата и время создания заказа в системе Uniteller в формате dd.mm.yyyy hh:mm:ss.
     */
    const DATE = 'Date';
    /**
     * Адрес электронной почты Держателя карты.
     */
    const EMAIL = 'Email';
    /**
     * Тип электронной валюты.
     *
     * @see \Tmconsulting\Uniteller\Payment\EMoneyType
     */
    const E_MONEY_TYPE = 'EMoneyType';
    /**
     * Данные заказа, выставленного в электронной платёжной системе. В формате «title1=value1, title2=value2, …».
     */
    const E_ORDER_DATA = 'EOrderData';
    /**
     * Код ответа процессингового центра.
     */
    const ERROR_CODE = 'Error_Code';
    /**
     * Расшифровка кода ответа процессингового центра.
     */
    const ERROR_COMMENT = 'Error_Comment';
    /**
     * Имя Держателя карты.
     */
    const FIRST_NAME = 'FirstName';
    /**
     * Фамилия Держателя карты.
     */
    const LAST_NAME = 'LastName';
    /**
     * Отчество Держателя карты.
     */
    const MIDDLE_NAME = 'MiddleName';
    /**
     * Назначение платежа через ГДС
     * «10» — оплата комиссионного вознаграждения Агентства;
     * «20» — оплата билетов;
     * для платежей без участия ГДС этот параметр пустой.
     * В ответе в формате SOAP на запрос результата авторизации в текущей версии сервиса этот параметр не возвращается.
     */
    const GDS_PAYMENT_PURPOSE_ID = 'gds_payment_purpose_id';
    /**
     * «Длинная запись» (параметр, включающий дополнительную информацию, необходимую при бронировании и оплате авиабилетов).
     */
    const I_DATA = 'IData';
    /**
     * IP-адрес Покупателя.
     */
    const IP_ADDRESS = 'IPAddress';
    /**
     * Идентификатор кредитной организации.
     */
    const LOAN_ID = 'LoanID';
    /**
     * Сообщение об ошибке (текст ошибки, если она произошла).
     */
    const MESSAGE = 'Message';
    /**
     * Признак необходимости подтверждения преавторизации.
     * «0» — платёж без преавторизации или уже подтверждён;
     * «1» — необходимо подтверждение.
     * В ответе в формате SOAP на запрос результата авторизации в текущей версии сервиса этот параметр не возвращается.
     */
    const NEED_CONFIRM = 'need_confirm';
    /**
     * Номер заказа в интернет-магазине Мерчанта.
     */
    const ORDER_NUMBER = 'OrderNumber';
    /**
     * Идентификатор «родительского» платежа (значение параметра OrderNumber) для рекуррентного платежа.
     * Пустое значение, если платёж нерекуррентный.
     * В ответе в формате SOAP на запрос результата авторизации в текущей версии сервиса этот параметр не возвращается.
     */
    const PARENT_ORDER_NUMBER = 'parent_order_number';
    /**
     * В ответе в формате SOAP на запрос результата авторизации в текущей версии сервиса этот параметр не возвращается.
     *
     * @see \Tmconsulting\Uniteller\Payment\PaymentType
     */
    const PAYMENT_TYPE = 'PaymentType';
    /**
     * Телефон Держателя карты.
     */
    const PHONE = 'Phone';
    /**
     * Тип платежа.
     */
    const PT_CODE = 'PT_Code';
    /**
     * Идентификатор QR-кода, выданный НСПК.
     */
    const QRC_ID = 'QrcId';
    /**
     * Описание чека фискализации, закодированное методом Base64.
     */
    const RECEIPT = 'Receipt';
    /**
     * Расшифровка кода возврата.
     */
    const RECOMMENDATION = 'Recommendation';
    /**
     * Код возврата.
     */
    const RESPONSE_CODE = 'Response_Code';
    /**
     * Уникальный номер заказа в Платёжном шлюзе.
     */
    const SBER_ORDER_ID = 'SberOrderId';
    /**
     * Сумма заказа.
     */
    const SUM = 'Sum';
    /**
     * Состояние заказа.
     */
    const STATUS = 'Status';
    /**
     * Сумма всех средств, уплаченных по одному заказу.
     * Десятичный разделитель — точка.
     */
    const TOTAL = 'Total';
    /**
     * Идентификатор банка-эквайера в системе Uniteller.
     */
    const ACQUIRER_ID = 'AcquirerID';
    /**
     * Неизвестно.
     */
    const CARD_SUB_TYPE = 'CardSubType';
    /**
     * Страна.
     */
    const COUNTRY = 'Country';
    /**
     * Неизвестно
     */
    const IS_OTHER_CARD = 'IsOtherCard';
    /**
     * Неизвестно
     */
    const PACKET_DATE = 'PacketDate';
    /**
     * Неизвестно
     */
    const PROCESSING_NAME = 'ProcessingName';
    /**
     * Неизвестно
     */
    const PROTOCOL_TYPE_NAME = 'ProtocolTypeName';
    /**
     * Неизвестно
     */
    const RATE = 'Rate';
}
