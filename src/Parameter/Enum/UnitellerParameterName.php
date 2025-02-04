<?php

namespace Tmconsulting\Uniteller\Parameter\Enum;

use Tmconsulting\Uniteller\Builder\Enum\EnumToArrayTrait;

/**
 * Наименование полей в Uniteller
 */
final class UnitellerParameterName
{
    use EnumToArrayTrait;

    /**
     * Идентификатор точки продажи в системе Uniteller.
     * Доступен Merchant в Личном кабинете в пункте меню «Точки продажи», столбец Uniteller Point ID.
     */
    const SHOP_IDP = 'Shop_IDP';

    /**
     * Тоже идентификатор точки продажи в системе Uniteller. Используется в запросе получения результатов.
     */
    const SHOP_ID = 'Shop_ID';

    /**
     * Идентификатор точки продажи в системе Uniteller. Используется в запросе подтверждения преавторизованного платежа
     * с чеком.
     */
    const SHOPID = 'ShopID';

    /**
     * Идентификатор точки продажи в системе Uniteller. Используется в запросе отмены платежа с чеком.
     */
    const UPID = 'UPID';

    /**
     * Идентификатор заказа в системе Merchant, соответствующий данному платежу. Может быть любой непустой строкой
     * максимальной длиной 127 символов, не может содержать только пробелы.
     */
    const ORDER_IDP = 'Order_IDP';

    /**
     * Тоже идентификатор заказа в системе Merchant, используется в других запросах, например запрос на отмену.
     */
    const ORDER_ID = 'OrderID';

    /**
     * Тоже идентификатор заказа в системе Merchant, используется в запросе получения результатов.
     */
    const SHOP_ORDER_NUMBER = 'ShopOrderNumber';

    /**
     * Сумма покупки в валюте, оговоренной в договоре с банком-эквайером.
     * В качестве десятичного разделителя используется точка, не более 2 знаков после разделителя. Например, 12.34
     */
    const SUBTOTAL_P = 'Subtotal_P';

    /**
     * Используется в запросе отмены, подтверждения.
     */
    const SUBTOTAL = 'Subtotal';

    /**
     * Сигнатура.
     */
    const SIGNATURE = 'Signature';

    /**
     * Сигнатура чека.
     */
    const RECEIPT_SIGNATURE = 'ReceiptSignature';

    /**
     * URL страницы, на которую должен вернуться Покупатель после успешного осуществления платежа (до 255 символов).
     */
    const URL_RETURN_OK = 'URL_RETURN_OK';

    /**
     * URL страницы, на которую должен вернуться Покупатель после неуспешного осуществления платежа (до 255 символов).
     */
    const URL_RETURN_NO = 'URL_RETURN_NO';

    /**
     * URL страницы, на которую должен вернуться Покупатель во всех случаях (до 255 символов).
     */
    const URL_RETURN = 'URL_RETURN';

    /**
     * Валюта платежа. Одно из \Tmconsulting\Uniteller\Parameter\Enum\Currency
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\Currency
     */
    const CURRENCY = 'Currency';

    /**
     * Email (до 64 символов).
     */
    const EMAIL = 'Email';

    /**
     * Время жизни формы оплаты в секундах, начиная с момента её показа. Должно быть целым положительным числом.
     */
    const LIFETIME = 'Lifetime';

    /**
     * Время жизни (в секундах) заказа на оплату банковской картой, начиная с момента первого вывода формы оплаты.
     */
    const ORDER_LIFETIME = 'OrderLifetime';

    /**
     * Идентификатор Покупателя, используемый некоторыми интернет-магазинами (до 64 символов).
     */
    const CUSTOMER_IDP = 'Customer_IDP';

    /**
     * Идентификатор зарегистрированной карты (до 128 символов).
     */
    const CARD_IDP = 'Card_IDP';

    /**
     * Внешний номер заказа Merchant-а (строка в кодировке UTF-8, не содержащая символов ";", "=").
     */
    const MERCHANT_ORDER_ID = 'MerchantOrderId';

    /**
     * Дополнительные данные («длинная запись»).
     */
    const I_DATA = 'IData';

    /**
     * Тип платежа. Произвольная строка длиной до 10 символов включительно.
     * В подавляющем большинстве схем подключения интернет-магазинов этот параметр не используется.
     */
    const PT_CODE = 'PT_Code';

    /**
     * Платёжная система кредитной карты.
     * Необязательный параметр, влияющий на выбор способа оплаты и отображение платёжной формы.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\MeanType
     */
    const MEAN_TYPE = 'MeanType';

    /**
     * Тип электронной валюты.
     * Необязательный параметр, влияющий на выбор способа оплаты и отображение платёжной формы.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\EMoneyType
     */
    const E_MONEY_TYPE = 'EMoneyType';

    /**
     * Разрешенные типы платежей.
     * JSON-объект формата:
     * {
     *     тип_платежа1: [
     *         сумма_заказа,
     *         сумма_заказа
     *     ],
     *     ...
     * }
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentType
     */
    const PAYMENT_TYPE_LIMITS = 'PaymentTypeLimits';

    /**
     * Срок жизни заказа оплаты в электронной платёжной системе в часах. Допустимое значение: от 1 до 1080.
     */
    const BILL_LIFETIME = 'BillLifetime';

    /**
     * Признак преавторизации платежа.
     * В запросе передается значение "1".
     */
    const PREAUTH = 'Preauth';

    /**
     * Признак того, что платеж является "родительским" для последующих рекуррентных платежей.
     * В запросе передается значение "1".
     */
    const IS_RECURRENT_START = 'IsRecurrentStart';

    /**
     * Список дополнительных полей, передаваемых в уведомлении об изменении статуса заказа.
     * Например - "BillNumber ApprovalCode Total".
     */
    const CALLBACK_FIELDS = 'CallbackFields';

    /**
     *  Запрашиваемый формат уведомления о статусе оплаты.
     *  Если параметр имеет значение "json", то уведомление направляется в json-формате.
     *  Во всех остальных случаях уведомление направляется в виде POST-запроса.
     */
    const CALLBACK_FORMAT = 'CallbackFormat';

    /**
     * Адрес для возврата Плательщика после оплаты через СБП или SberPay в банковском приложении (до 255 символов).
     */
    const BACK_URL = 'BackUrl';
    /**
     * Ссылка на мобильное приложение Клиента для возврата после оплаты через СБП или SberPay (до 255 символов).
     * Если не задано, возврат будет выполнен по ссылке из параметра BackUrl
     */
    const DEEP_LINK = 'DeepLink';

    /**
     * Код языка интерфейса платёжной страницы (2 символа).
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\Language
     */
    const LANGUAGE = 'Language';

    /**
     * Номер кошелька получателя электронных денежных средств. Если в свойствах точки продажи включён
     * параметр «Квази-кэш» и Merchant-ом этот параметр не передан, то на странице оплаты отображается обязательное
     * поле для указания номер кошелька.
     * Латинские буквы и цифры.
     */
    const E_WALLET = 'EWallet';

    /**
     * Номер телефона, для которого производится пополнение баланса. Если в свойствах точки продажи включён параметр
     * «Телекоммуникационные услуги» и Merchant-ом этот параметр не передан, то на странице оплаты отображается
     * обязательное поле для указания номера телефона.
     *
     * Формат телефонного номера с кодом страны: +7XXXXXXXXXX
     */
    const DEST_PHONE_NUM = 'DestPhoneNum';

    /**
     * Комментарий к платежу (до 1024 символов)
     */
    const COMMENT = 'Comment';

    /**
     * Имя Покупателя, переданное от Merchant (до 64 символа).
     */
    const FIRST_NAME = 'FirstName';

    /**
     * Фамилия Покупателя, переданная от Merchant (до 64 символов).
     */
    const LAST_NAME = 'LastName';

    /**
     * Отчество Покупателя, переданное от Merchant (до 64 символов).
     */
    const MIDDLE_NAME = 'MiddleName';

    /**
     * Телефон покупателя (до 64 символов).
     */
    const PHONE = 'Phone';

    /**
     * Верифицированный Merchant-ом номер телефона (до 64 символов).
     */
    const PHONE_VERIFIED = 'PhoneVerified';

    /**
     * Адрес (до 128 символов).
     */
    const ADDRESS = 'Address';

    /**
     * Название страны Покупателя (до 64 символов).
     */
    const COUNTRY = 'Country';

    /**
     * Код штата/региона (до 3-х символов).
     */
    const STATE = 'State';

    /**
     * Город (до 64 символов).
     */
    const CITY = 'City';

    /**
     * Почтовый индекс (до 64 символов).
     */
    const ZIP = 'Zip';

    /**
     * Пароль. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
     */
    const PASSWORD = 'Password';

    /**
     * Логин. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
     */
    const LOGIN = 'Login';

    /**
     * Формат ответа.
     */
    const FORMAT = 'Format';

    /**
     * Какие операции включать в ответ.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\Success
     */
    const SUCCESS = 'Success';

    const START_DAY = 'StartDay';

    const START_MONTH = 'StartMonth';

    const START_YEAR = 'StartYear';

    const START_HOUR = 'StartHour';

    const START_MIN = 'StartMin';

    const END_DAY = 'EndDay';

    const END_MONTH = 'EndMonth';

    const END_YEAR = 'EndYear';

    const END_HOUR = 'EndHour';

    const END_MIN = 'EndMin';

    const START_DAY_OF_CHANGE = 'StartDayOfChange';

    const START_MONTH_OF_CHANGE = 'StartMonthOfChange';

    const START_YEAR_OF_CHANGE = 'StartYearOfChange';

    const START_HOUR_OF_CHANGE = 'StartHourOfChange';

    const START_MIN_OF_CHANGE = 'StartMinOfChange';

    const END_DAY_OF_CHANGE = 'EndDayOfChange';

    const END_MONTH_OF_CHANGE = 'EndMonthOfChange';

    const END_YEAR_OF_CHANGE = 'EndYearOfChange';

    const END_HOUR_OF_CHANGE = 'EndHourOfChange';

    const END_MIN_OF_CHANGE = 'EndMinOfChange';

    /**
     * Фильтр результатов оплат (последняя, успешные, все, с возвратами).
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentsResults
     */
    const PAYMENTS_RESULTS = 'PaymentsResults';

    /**
     * Будет ли возвращаться в ответе параметр Partly canceled в случае частичной отмены/возврата платежа.
     * Возможные значения: 1 или 0 (или отсутствует)
     */
    const SHOW_PARTLY_CANCELED = 'ShowPartlyCanceled';

    /**
     * Режим выдачи результата.
     * 0 — браузер, 1 — файл, 2 — архивированный файл. По умолчанию 0.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\ZipFlag
     */
    const ZIP_FLAG = 'ZipFlag';

    /**
     * Будут ли возвращаться в ответе параметры запроса.
     * 0 — нет,
     * 1 — да
     * По умолчанию 0.
     */
    const HEADER = 'Header';

    /**
     * Будут ли возвращаться в ответе заголовки полей.
     * 0 — нет,
     * 1 — да
     * По умолчанию 0.
     */
    const HEADER1 = 'Header1';

    /**
     * Разделитель полей в CVS-формате. Возможные варианты «;», «,», «:», «/».
     */
    const DELIMITER = 'Delimiter';

    /**
     * Открывающий разделитель полей в формате «в скобках». Возможные варианты «[», «{», «(».
     */
    const OPEN_DELIMITER = 'OpenDelimiter';

    /**
     * Закрывающий разделитель полей в формате «в скобках». Возможные варианты «]», «}», «)».
     */
    const CLOSE_DELIMITER = 'CloseDelimiter';

    /**
     * Разделитель строк. Возможные варианты «13», «10», «13,10», «10,13». По умолчанию «13,10».
     */
    const ROW_DELIMITER = 'RowDelimiter';

    /**
     * Набор информационных полей, возвращаемых в ответе на запрос.
     * Если параметр не передаётся или передаётся пустое значение, то будет возвращён полный список полей.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\SFields
     */
    const S_FIELDS = 'S_FIELDS';

    /**
     * Номер платежа в системе Uniteller (RRN).
     * 12 цифр.
     */
    const BILLNUMBER = 'Billnumber';

    /**
     * Причина отмена операции.
     */
    const RVR_REASON = 'RVRReason';

    const PARENT_ORDER_IDP = 'Parent_Order_IDP';

    /**
     * Идентификатор точки продажи в системе Uniteller, через которую был проведён «родительский» платёж.
     *
     * Если рекуррентный платёж осуществляется через ту же точку продажи, что и «родительский» платёж,
     * то параметр Parent_Shop_IDP можно не передавать.
     */
    const PARENT_SHOP_IDP = 'Parent_Shop_IDP';

    /**
     * Чек
     */
    const RECEIPT = 'Receipt';
}
