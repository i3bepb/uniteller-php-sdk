<?php

namespace Tmconsulting\Uniteller\Payment;

use Tmconsulting\Uniteller\Builder\BaseBuilder;
use Tmconsulting\Uniteller\Exception\Configuration\ConfigurationException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\AmountParameter;
use Tmconsulting\Uniteller\Parameter\BooleanParameter;
use Tmconsulting\Uniteller\Parameter\CallbackFieldsParameter;
use Tmconsulting\Uniteller\Parameter\Enum\CallbackFormat;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\Currency;
use Tmconsulting\Uniteller\Parameter\Enum\EMoneyType;
use Tmconsulting\Uniteller\Parameter\Enum\Language;
use Tmconsulting\Uniteller\Parameter\Enum\MeanType;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentType;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\EnumParameter;
use Tmconsulting\Uniteller\Parameter\IntParameter;
use Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter;
use Tmconsulting\Uniteller\Parameter\PhoneParameter;
use Tmconsulting\Uniteller\Parameter\ReceiptParameter;
use Tmconsulting\Uniteller\Parameter\ScalarParameter;
use Tmconsulting\Uniteller\Parameter\StringParameter;
use Tmconsulting\Uniteller\Parameter\UrlParameter;

/**
 * Описывает сценарии (кейсы) инициации оплаты через платёжную форму в Uniteller.
 * Позволяет сконфигурировать параметры платежа и получить URL платёжной формы для перехода пользователя.
 *
 * @method $this setShopId(string|int $shopId) Идентификатор точки продажи в системе Uniteller.
 * @method string|null getShopId()
 * @method bool hasShopId()
 *
 * @method $this setPassword(string|int $password) Пароль. Доступен Merchant-у в Личном кабинете.
 * @method string|null getPassword()
 * @method bool hasPassword()
 *
 * @method $this setLogin(string|int $login) Логин. Доступен Merchant-у в Личном кабинете.
 * @method string|null getLogin()
 * @method bool hasLogin()
 *
 * @method $this setAddress(string $address) Адрес покупателя, до 128 символов
 * @method string|null getAddress()
 * @method bool hasAddress()
 *
 * @method $this setBackUrl(string $backUrl) Адрес для возврата плательщика после оплаты через СБП или SberPay в банковском приложении, до 255 символов
 * @method string|null getBackUrl()
 * @method bool hasBackUrl()
 *
 * @method $this setBillLifetime(int $billLifetime) Срок жизни заказа оплаты в электронной платёжной системе в часах, от 1 до 1080
 * @method int|null getBillLifetime()
 * @method bool hasBillLifetime()
 *
 * @method $this setCallbackFields(array $callbackFields) Список дополнительных полей, передаваемых в уведомлении об изменении статуса заказа
 * @method mixed getCallbackFields()
 * @method bool hasCallbackFields()
 *
 * @method $this setCallbackFormat(string $callbackFormat) Запрашиваемый формат уведомления о статусе оплаты
 * @method string|null getCallbackFormat()
 * @method bool hasCallbackFormat()
 *
 * @method $this setCardId(string|int $cardId) Идентификатор зарегистрированной карты, до 128 символов
 * @method string|null getCardId()
 * @method bool hasCardId()
 *
 * @method $this setCity(string $city) Город покупателя, до 64 символов
 * @method string|null getCity()
 * @method bool hasCity()
 *
 * @method $this setComment(string $comment) Комментарий к платежу, до 1024 символов
 * @method string|null getComment()
 * @method bool hasComment()
 *
 * @method $this setCountry(string $country) Название страны покупателя, до 64 символов
 * @method string|null getCountry()
 * @method bool hasCountry()
 *
 * @method $this setCurrency(string $currency) Валюта платежа
 * @method string|null getCurrency()
 * @method bool hasCurrency()
 *
 * @method $this setCustomerId(string|int $customerId) Идентификатор покупателя, используемый некоторыми интернет-магазинами, до 64 символов
 * @method string|null getCustomerId()
 * @method bool hasCustomerId()
 *
 * @method $this setDeepLink(string $deepLink) Ссылка на мобильное приложение клиента для возврата после оплаты через СБП или SberPay, до 255 символов
 * @method string|null getDeepLink()
 * @method bool hasDeepLink()
 *
 * @method $this setDestPhoneNum(string $destPhoneNum) Номер телефона для пополнения баланса в формате +7XXXXXXXXXX
 * @method string|null getDestPhoneNum()
 * @method bool hasDestPhoneNum()
 *
 * @method $this setEmail(string $email) Email покупателя, до 64 символов
 * @method string|null getEmail()
 * @method bool hasEmail()
 *
 * @method $this setEMoneyType(string $eMoneyType) Тип электронной валюты.
 * @method string|null getEMoneyType()
 * @method bool hasEMoneyType()
 *
 * @method $this setEWallet(string $eWallet) Номер кошелька получателя электронных денежных средств, до 64 символов
 * @method string|null getEWallet()
 * @method bool hasEWallet()
 *
 * @method $this setFirstName(string $firstName) Имя покупателя, переданное от Merchant, до 64 символов
 * @method string|null getFirstName()
 * @method bool hasFirstName()
 *
 * @method $this setIData(string $iData) Дополнительные данные, длинная запись
 * @method string|null getIData()
 * @method bool hasIData()
 *
 * @method $this setIsRecurrentStart(bool $isRecurrentStart) Признак того, что платёж является родительским для последующих рекуррентных платежей
 * @method bool|null getIsRecurrentStart()
 * @method bool hasIsRecurrentStart()
 *
 * @method $this setLanguage(string $language) Код языка интерфейса платёжной страницы
 * @method string|null getLanguage()
 * @method bool hasLanguage()
 *
 * @method $this setLastName(string $lastName) Фамилия покупателя, переданная от Merchant, до 64 символов
 * @method string|null getLastName()
 * @method bool hasLastName()
 *
 * @method $this setLifetime(int $lifetime) Время жизни формы оплаты в секундах, начиная с момента её показа
 * @method int|null getLifetime()
 * @method bool hasLifetime()
 *
 * @method $this setMeanType(string $meanType) Платёжная система кредитной карты
 * @method string|null getMeanType()
 * @method bool hasMeanType()
 *
 * @method $this setMerchantOrderId(string|int $merchantOrderId) Внешний номер заказа Merchant, до 256 символов, строка UTF-8 без символов ";" и "="
 * @method string|null getMerchantOrderId()
 * @method bool hasMerchantOrderId()
 *
 * @method $this setMiddleName(string $middleName) Отчество покупателя, переданное от Merchant, до 64 символов
 * @method string|null getMiddleName()
 * @method bool hasMiddleName()
 *
 * @method $this setOrderLifetime(int $orderLifetime) Время жизни заказа на оплату банковской картой в секундах, начиная с момента первого вывода формы оплаты
 * @method int|null getOrderLifetime()
 * @method bool hasOrderLifetime()
 *
 * @method $this setOrderId(string|int $orderId) Идентификатор заказа в системе Merchant, соответствующий данному платежу
 * @method string|null getOrderId()
 * @method bool hasOrderId()
 *
 * @method $this setPaymentTypeLimits(array $paymentTypeLimits) Разрешённые типы платежей в формате JSON-объекта
 * @method array|null getPaymentTypeLimits()
 * @method bool hasPaymentTypeLimits()
 *
 * @method $this setPhone(string $phone) Телефон покупателя, до 64 символов
 * @method string|null getPhone()
 * @method bool hasPhone()
 *
 * @method $this setPhoneVerified(string $phoneVerified) Верифицированный Merchant номер телефона, до 64 символов
 * @method string|null getPhoneVerified()
 * @method bool hasPhoneVerified()
 *
 * @method $this setPtCode(string $ptCode) Тип платежа, произвольная строка до 10 символов
 * @method string|null getPtCode()
 * @method bool hasPtCode()
 *
 * @method $this setState(string $state) Код штата или региона, до 3 символов
 * @method string|null getState()
 * @method bool hasState()
 *
 * @method string|null getSubtotal()
 * @method bool hasSubtotal()
 *
 * @method $this setUrlReturnOk(string $urlReturnOk) URL страницы, на которую должен вернуться покупатель после успешной оплаты, до 255 символов
 * @method string|null getUrlReturnOk()
 * @method bool hasUrlReturnOk()
 *
 * @method $this setUrlReturnNo(string $urlReturnNo) URL страницы, на которую должен вернуться покупатель после неуспешной оплаты, до 255 символов
 * @method string|null getUrlReturnNo()
 * @method bool hasUrlReturnNo()
 *
 * @method $this setUrlReturn(string $urlReturn) URL страницы, на которую должен вернуться покупатель после оплаты во всех случаях, до 255 символов
 * @method string|null getUrlReturn()
 * @method bool hasUrlReturn()
 *
 * @method $this setZip(string|int $zip) Почтовый индекс, до 64 символов
 * @method string|null getZip()
 * @method bool hasZip()
 *
 * @method $this setPreAuth(bool $preAuth) Признак преавторизации платежа
 * @method bool|null getPreAuth()
 * @method bool hasPreAuth()
 *
 * @method $this setReceipt(\Tmconsulting\Uniteller\Receipt\Receipt $receipt) Чек
 * @method bool|null getReceipt()
 * @method bool hasReceipt()
 */
class PaymentBuilder extends BaseBuilder
{
    /**
     * @var bool Чек обязательный параметр.
     */
    private $requireReceipt = false;

    /**
     * @var string|null
     */
    private $paymentType = null;

    /**
     * @return void
     */
    protected function registerParameters(): void
    {
        // Логин. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::LOGIN, UnitellerParameterName::LOGIN, null, false)
        );
        // Пароль. Доступен Merchant-у в Личном кабинете, пункт меню «Параметры Авторизации».
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PASSWORD, UnitellerParameterName::PASSWORD, null, false)
        );
        // Идентификатор точки продажи в системе Uniteller.
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::SHOP_ID, UnitellerParameterName::SHOP_IDP)
        );
        // Адрес (до 128 символов)
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::ADDRESS, UnitellerParameterName::ADDRESS, 128)
        );
        // Адрес для возврата Плательщика после оплаты через СБП или SberPay в банковском приложении (до 255 символов).
        $this->parameters->add(
            new UrlParameter(CanonicalParameterName::BACK_URL, UnitellerParameterName::BACK_URL, 255)
        );
        // Срок жизни заказа оплаты в электронной платёжной системе в часах. Допустимое значение: от 1 до 1080.
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::BILL_LIFETIME, UnitellerParameterName::BILL_LIFETIME, 255)
        );
        /**
         * Список дополнительных полей, передаваемых в уведомлении об изменении статуса заказа.
         * Например - "BillNumber ApprovalCode Total".
         */
        $this->parameters->add(
            new CallbackFieldsParameter(
                CanonicalParameterName::CALLBACK_FIELDS,
                UnitellerParameterName::CALLBACK_FIELDS
            )
        );
        // Запрашиваемый формат уведомления о статусе оплаты.
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::CALLBACK_FORMAT,
                UnitellerParameterName::CALLBACK_FORMAT,
                CallbackFormat::toArray()
            )
        );
        // Идентификатор зарегистрированной карты (до 128 символов).
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::CARD_ID, UnitellerParameterName::CARD_IDP, 128)
        );
        // Город (до 64 символов)
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::CITY, UnitellerParameterName::CITY, 64)
        );
        // Комментарий к платежу (до 1024 символов)
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::COMMENT, UnitellerParameterName::COMMENT, 1024)
        );
        // Название страны Покупателя (до 64 символов)
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::COUNTRY, UnitellerParameterName::COUNTRY, 64)
        );
        /**
         * Валюта платежа. Одно из \Tmconsulting\Uniteller\Parameter\Enum\Currency.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\Currency
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::CURRENCY,
                UnitellerParameterName::CURRENCY,
                Currency::toArray()
            )
        );
        // Идентификатор Покупателя, используемый некоторыми интернет-магазинами (до 64 символов).
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::CUSTOMER_ID, UnitellerParameterName::CUSTOMER_IDP, 64)
        );
        /**
         * Ссылка на мобильное приложение Клиента для возврата после оплаты через СБП или SberPay (до 255 символов).
         * Если не задано, возврат будет выполнен по ссылке из параметра BackUrl
         */
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::DEEP_LINK, UnitellerParameterName::DEEP_LINK, 255)
        );
        /**
         * Номер телефона, для которого производится пополнение баланса. Если в свойствах точки продажи включён параметр
         * «Телекоммуникационные услуги» и Merchant-ом этот параметр не передан, то на странице оплаты отображается
         * обязательное поле для указания номера телефона.
         *
         * Формат телефонного номера с кодом страны: +7XXXXXXXXXX
         */
        $this->parameters->add(
            new PhoneParameter(CanonicalParameterName::DEST_PHONE_NUM, UnitellerParameterName::DEST_PHONE_NUM)
        );
        // Email (до 64 символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::EMAIL, UnitellerParameterName::EMAIL, 64)
        );
        /**
         * Тип электронной валюты.
         * Необязательный параметр, влияющий на выбор способа оплаты и отображение платёжной формы.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\EMoneyType
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::E_MONEY_TYPE,
                UnitellerParameterName::E_MONEY_TYPE,
                EMoneyType::toArray()
            )
        );
        /**
         * Номер кошелька получателя электронных денежных средств. Если в свойствах точки продажи включён
         * параметр «Квази-кэш» и Merchant-ом этот параметр не передан, то на странице оплаты отображается обязательное
         * поле для указания номер кошелька (латинские буквы и цифры).
         */
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::E_WALLET, UnitellerParameterName::E_WALLET, 64)
        );
        // Имя Покупателя, переданное от Merchant (до 64 символа).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::FIRST_NAME, UnitellerParameterName::FIRST_NAME, 64)
        );
        // Дополнительные данные («длинная запись»).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::I_DATA, UnitellerParameterName::I_DATA)
        );
        /**
         * Признак того, что платеж является "родительским" для последующих рекуррентных платежей.
         * В запросе передается значение "1".
         */
        $this->parameters->add(
            new BooleanParameter(CanonicalParameterName::IS_RECURRENT_START, UnitellerParameterName::IS_RECURRENT_START)
        );
        /**
         * Код языка интерфейса платёжной страницы (2 символа).
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\Language
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::LANGUAGE,
                UnitellerParameterName::LANGUAGE,
                Language::toArray()
            )
        );
        // Фамилия Покупателя, переданная от Merchant (до 64 символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::LAST_NAME, UnitellerParameterName::LAST_NAME, 64)
        );
        // Время жизни формы оплаты в секундах, начиная с момента её показа. Должно быть целым положительным числом.
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::LIFETIME, UnitellerParameterName::LIFETIME, 1)
        );
        /**
         * Платёжная система кредитной карты.
         * Необязательный параметр, влияющий на выбор способа оплаты и отображение платёжной формы.
         *
         * @see \Tmconsulting\Uniteller\Parameter\Enum\MeanType
         */
        $this->parameters->add(
            new EnumParameter(
                CanonicalParameterName::MEAN_TYPE,
                UnitellerParameterName::MEAN_TYPE,
                MeanType::toArray()
            )
        );
        // Внешний номер заказа Merchant-а (строка в кодировке UTF-8, не содержащая символов ";", "=").
        $this->parameters->add(
            new ScalarParameter(
                CanonicalParameterName::MERCHANT_ORDER_ID,
                UnitellerParameterName::MERCHANT_ORDER_ID,
                256,
                ';='
            )
        );
        // Отчество Покупателя, переданное от Merchant (до 64 символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::MIDDLE_NAME, UnitellerParameterName::MIDDLE_NAME, 64)
        );
        // Время жизни (в секундах) заказа на оплату банковской картой, начиная с момента первого вывода формы оплаты.
        $this->parameters->add(
            new IntParameter(CanonicalParameterName::ORDER_LIFETIME, UnitellerParameterName::ORDER_LIFETIME, 1)
        );
        /**
         * Идентификатор заказа в системе Merchant, соответствующий данному платежу. Может быть любой непустой строкой
         * максимальной длиной 127 символов, не может содержать только пробелы.
         */
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::ORDER_ID, UnitellerParameterName::ORDER_IDP, 127)
        );
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
        $this->parameters->add(
            new PaymentTypeLimitsParameter(
                CanonicalParameterName::PAYMENT_TYPE_LIMITS,
                UnitellerParameterName::PAYMENT_TYPE_LIMITS
            )
        );
        // Телефон покупателя (до 64 символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PHONE, UnitellerParameterName::PHONE, 64)
        );
        // Верифицированный Merchant-ом номер телефона (до 64 символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PHONE_VERIFIED, UnitellerParameterName::PHONE_VERIFIED, 64)
        );
        // Тип платежа. Произвольная строка (до 10 символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::PT_CODE, UnitellerParameterName::PT_CODE, 10)
        );
        // Код штата/региона (до 3-х символов).
        $this->parameters->add(
            new StringParameter(CanonicalParameterName::STATE, UnitellerParameterName::STATE, 3)
        );
        /**
         * Сумма покупки в валюте, оговоренной в договоре с банком-эквайером.
         * В качестве десятичного разделителя используется точка, не более 2 знаков после разделителя. Например, 12.34
         */
        $this->parameters->add(
            new AmountParameter(CanonicalParameterName::SUBTOTAL, UnitellerParameterName::SUBTOTAL_P)
        );
        // URL страницы, на которую должен вернуться Покупатель после успешного осуществления платежа (до 255 символов).
        $this->parameters->add(
            new UrlParameter(CanonicalParameterName::URL_RETURN_OK, UnitellerParameterName::URL_RETURN_OK, 255)
        );
        // URL страницы, на которую должен вернуться Покупатель после неуспешного осуществления платежа (до 255 символов).
        $this->parameters->add(
            new UrlParameter(CanonicalParameterName::URL_RETURN_NO, UnitellerParameterName::URL_RETURN_NO, 255)
        );
        // URL страницы, на которую должен вернуться Покупатель во всех случаях (до 255 символов).
        $this->parameters->add(
            new UrlParameter(CanonicalParameterName::URL_RETURN, UnitellerParameterName::URL_RETURN, 255)
        );
        // Почтовый индекс (до 64 символов).
        $this->parameters->add(
            new ScalarParameter(CanonicalParameterName::ZIP, UnitellerParameterName::ZIP, 64)
        );
        /**
         * Признак преавторизации платежа. В запросе передается значение "1".
         */
        $this->parameters->add(
            new BooleanParameter(CanonicalParameterName::PREAUTH, UnitellerParameterName::PREAUTH)
        );
        // Чек
        $this->parameters->add(
            new ReceiptParameter(CanonicalParameterName::RECEIPT, UnitellerParameterName::RECEIPT)
        );
    }

    /**
     * Проверка присутствия обязательных полей.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    protected function validateRequired(): void
    {
        if (!$this->hasPassword()) {
            throw new RequiredParameterException(UnitellerParameterName::PASSWORD);
        }
        if (!$this->hasShopId()) {
            throw new RequiredParameterException(UnitellerParameterName::SHOP_IDP);
        }
        if (!$this->hasOrderId()) {
            throw new RequiredParameterException(UnitellerParameterName::ORDER_IDP);
        }
        if (!$this->hasSubtotal()) {
            throw new RequiredParameterException(UnitellerParameterName::SUBTOTAL_P);
        }
        if (!$this->hasUrlReturn() && !($this->hasUrlReturnOk() && $this->hasUrlReturnNo())) {
            throw new RequiredParameterException(
                UnitellerParameterName::URL_RETURN
                . ' or '
                . UnitellerParameterName::URL_RETURN_OK . '/' . UnitellerParameterName::URL_RETURN_NO
            );
        }
        if ($this->requireReceipt) {
            if (!$this->hasReceipt()) {
                throw new RequiredParameterException(UnitellerParameterName::RECEIPT);
            }
        }
    }

    /**
     * Возвращает сигнатуру.
     * Порядок следования полей важен т.к. иначе signature может не пройти!
     * @todo: Индексы используется только... переделать get... на получение всего объекта параметра и использовать индексы только в отладке, чтобы они тут не торчали
     *
     * @return string Сигнатура.
     */
    public function getSignature(): string
    {
        $arr = [
            UnitellerParameterName::SHOP_IDP     => $this->getShopId(),
            UnitellerParameterName::ORDER_IDP    => $this->getOrderId(),
            UnitellerParameterName::SUBTOTAL_P   => $this->getSubtotal(),
            UnitellerParameterName::MEAN_TYPE    => $this->getMeanType(),
            UnitellerParameterName::E_MONEY_TYPE => $this->getEMoneyType(),
            UnitellerParameterName::LIFETIME     => $this->getLifetime(),
            UnitellerParameterName::CUSTOMER_IDP => $this->getCustomerId(),
            UnitellerParameterName::CARD_IDP     => $this->getCardId(),
            UnitellerParameterName::I_DATA       => $this->getIData(),
            UnitellerParameterName::PT_CODE      => $this->getPtCode(),
        ];
        if ($this->hasOrderLifetime()) {
            $arr[UnitellerParameterName::ORDER_LIFETIME] = $this->getOrderLifetime();
        }
        if ($this->hasPhoneVerified()) {
            $arr[UnitellerParameterName::PHONE_VERIFIED] = $this->getPhoneVerified();
        }
        if ($this->hasPaymentTypeLimits()) {
            $arr[UnitellerParameterName::PAYMENT_TYPE_LIMITS] = $this->getPaymentTypeLimits();
        }
        if ($this->hasMerchantOrderId()) {
            $arr[UnitellerParameterName::MERCHANT_ORDER_ID] = $this->getMerchantOrderId();
        }
        $arr[UnitellerParameterName::PASSWORD] = $this->getPassword();
        return $this->signatureCreator->setParameters($arr)->createMd5();
    }

    /**
     * @return string Сигнатура чека.
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getReceiptSignature(): string
    {
        $arr = [
            UnitellerParameterName::SHOP_IDP   => $this->getShopId(),
            UnitellerParameterName::ORDER_IDP  => $this->getOrderId(),
            UnitellerParameterName::SUBTOTAL_P => $this->getSubtotal(),
            UnitellerParameterName::RECEIPT    => $this->getReceipt(),
            UnitellerParameterName::PASSWORD   => $this->getPassword(),
        ];

        return $this->signatureCreator->setParameters($arr)->createSha256();
    }

    /**
     * Возвращает массив со значениями параметров.
     *
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr[UnitellerParameterName::SIGNATURE] = $this->getSignature();
        if ($this->hasReceipt()) {
            $arr[UnitellerParameterName::RECEIPT_SIGNATURE] = $this->getReceiptSignature();
        }

        return $arr;
    }

    /**
     * @param bool $requireReceipt
     *
     * @return static
     */
    public function requireReceipt(bool $requireReceipt = true)
    {
        $this->requireReceipt = $requireReceipt;

        return $this;
    }

    /**
     * Устанавливает тип оплаты для ограничения платёжного сценария.
     * Значение используется для автоматического формирования параметра PaymentTypeLimits на основе текущей суммы.
     *
     * @param string $paymentType Тип оплаты из \Tmconsulting\Uniteller\Parameter\Enum\PaymentType.
     *
     * @return static
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Configuration\ConfigurationException
     */
    public function setPaymentType(string $paymentType)
    {
        $types = PaymentType::toArray();
        if (!in_array($paymentType, $types, true)) {
            throw new ConfigurationException(
                "Invalid PaymentType: unsupported payment type \"{$paymentType}\". Allowed values: "
                . implode(', ', $types) . '.'
            );
        }
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @param string|int $subtotal Сумма покупки в валюте договора с банком-эквайером, не более 2 знаков после точки.
     *
     * @return static
     */
    public function setSubtotal($subtotal)
    {
        $parameter = $this->parameters->get(CanonicalParameterName::SUBTOTAL);
        $parameter->setValue($subtotal);

        $this->applyPaymentTypeLimits();

        return $this;
    }

    /**
     * Добавляем параметр PaymentTypeLimits в зависимости от $this->paymentType.
     *
     * @return void
     */
    private function applyPaymentTypeLimits(): void
    {
        if ($this->paymentType === null) {
            return;
        }

        $sum = $this->getSubtotal();

        $this->parameters
            ->get(CanonicalParameterName::PAYMENT_TYPE_LIMITS)
            ->setValue([
                $this->paymentType => [$sum, $sum],
            ]);
    }

    /**
     * @return \Tmconsulting\Uniteller\Payment\Uri
     */
    public function process(): Uri
    {
        $parameters = $this->toArray();

        if ($this->debug) {
            $this->logger->debug('Parameters in request: ' . PHP_EOL . print_r($parameters, true));
            if ($this->hasReceipt()) {
                $this->logger->debug(
                    'Receipt: ' . PHP_EOL . json_encode(
                        $this->getReceipt(),
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    )
                );
            }
        }

        $uri = sprintf('%s?%s', $this->getEndpoint(), http_build_query($parameters));

        return new Uri($uri);
    }
}
