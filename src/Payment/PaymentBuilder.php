<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Payment;

use Tmconsulting\Uniteller\ArraybleInterface;
use Tmconsulting\Uniteller\Common\Builder;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;

/**
 * Class PaymentBuilder
 *
 * @package Tmconsulting\Client\Payment
 */
final class PaymentBuilder implements ArraybleInterface, Builder
{
    /**
     * Shop_IDP (Client Point ID)
     * Текст, содержащий латинские буквы, цифры и "-"
     *
     * @var string
     */
    private $shopIdp;

    /**
     * Номер заказа в системе расчётов интернет-магазина,
     * соответствующий данному платежу. Может быть любой непустой
     * строкой максимальной длиной 127 символов, не может содержать только пробелы.
     *
     * Значение Order_IDP должно быть уникальным для всех оплаченных заказов (заказов,
     * по которым успешно прошла блокировка средств) в рамках одного магазина (одной точки продажи).
     *
     * @var string
     */
    private $orderIdp = '';

    /**
     * Сумма покупки в валюте, оговоренной в договоре с банком-эквайером.
     * В качестве десятичного разделителя используется точка,
     * не более 2 знаков после разделителя. Например, 12.34
     *
     * @var float
     */
    private $subtotalP = 0;

    /**
     * URL страницы, на которую должен вернуться Покупатель
     * после успешного осуществления платежа в системе Client.
     * Длина до 255 символов.
     *
     * @var string
     */
    private $urlReturnOk;

    /**
     * URL страницы, на которую должен вернуться Покупатель
     * после неуспешного осуществления платежа в системе
     * Длина до 255 символов.
     *
     * @var string
     */
    private $urlReturnNo;

    /**
     * Валюта платежа.
     * RUB — российский рубль;
     * UAH — украинская гривна;
     * AZN — азербайджанский манат;
     * KZT — казахский тенге.
     *
     * @see \Tmconsulting\Uniteller\Payment\Currency
     *
     * @var string
     */
    private $currency;

    /**
     * Длина до 64 символа.
     *
     * @var string
     */
    private $email;

    /**
     * Время жизни формы оплаты в секундах, начиная с момента её показа.
     * Должно быть целым положительным числом
     *
     * @var int
     */
    private $lifetime;

    /**
     * Время жизни (в секундах) заказа на оплату банковской картой,
     * начиная с момента первого вывода формы оплаты.
     *
     * @var int
     */
    private $orderLifetime;

    /**
     * Идентификатор Покупателя, используемый некоторыми интернет-магазинами.
     * (64 символа)
     *
     * @var string
     */
    private $customerIdp;

    /**
     * Идентификатор зарегистрированной карты
     * (до 128 символов)
     *
     * @var string
     */
    private $cardIdp;

    /**
     * «длинная запись»
     *
     * @deprecated со слов представителя Uniteller "на данный момент можно не передавать idata"
     *
     * @var string
     */
    private $iData;

    /**
     * Тип платежа. Произвольная строка длиной до десяти символов включительно.
     * В подавляющем большинстве схем подключения интернет-магазинов этот параметр не используется.
     *
     * @var string
     */
    private $ptCode;

    /**
     * Платёжная система кредитной карты.
     * Может принимать значения: 0 — любая, 1 — VISA, 2 — MasterCard,
     * 3 — Diners Club, 4 — JCB, 5 — American Express.
     *
     * @see \Tmconsulting\Uniteller\Payment\MeanType
     *
     * @var int
     */
    private $meanType;

    /**
     * Тип электронной валюты.
     * 0 - Любая система электронных платежей
     * 1 - Яндекс.Деньги
     * 13 - Оплата наличными (Евросеть, Яндекс.Деньги и пр.)
     * 18 - QIWI Кошелек REST (по протоколу REST)
     * 29 - WebMoney WMR
     *
     * @see \Tmconsulting\Uniteller\Payment\EMoneyType
     *
     * @var int
     */
    private $eMoneyType;

    /**
     * Срок жизни заказа оплаты в электронной платёжной системе в часах (от 1 до 1080 часов).
     * Значение параметра BillLifetime учитывается только для QIWI-платежей.
     * Если BillLifetime не передаётся, то для QIWI-платежа срок жизни заказа на
     * оплату устанавливается по умолчанию — 72 часа.
     *
     * @var int
     */
    private $billLifetime;

    /**
     * Признак преавторизации платежа.
     * При использовании в запросе должен принимать значение “1”.
     *
     * @var bool
     */
    private $preAuth = false;

    /**
     * Признак того, что платёж является «родительским» для
     * последующих рекуррентных платежей. Может принимать значение “1”.
     *
     * @var bool
     */
    private $isRecurrentStart = false;

    /**
     * Список дополнительных полей, передаваемых в уведомлении об изменении статуса заказа.
     * Например - BillNumber, ApprovalCode, Total
     *
     * @see \Tmconsulting\Uniteller\Payment\CallbackFields
     *
     * @var string
     */
    private $callbackFields;

    /**
     * Запрашиваемый формат уведомления о статусе оплаты.
     * Если параметр имеет значение "json", то уведомление направляется в json-формате.
     * Во всех остальных случаях уведомление направляется в виде POST-запроса.
     *
     * @var string
     */
    private $callbackFormat;

    /**
     * Код языка интерфейса платёжной страницы. Может быть en или ru.
     * (2 символа)
     *
     * @see \Tmconsulting\Uniteller\Payment\Language
     *
     * @var string
     */
    private $language;

    /**
     * Комментарий к платежу
     * (до 1024 символов)
     *
     * @var string
     */
    private $comment;

    /**
     * Имя Покупателя, переданное с сайта Мерчанта
     * (64 символа)
     *
     * @var string
     */
    private $firstName;

    /**
     * Фамилия Покупателя, переданная с сайта Мерчанта
     * (64 символа)
     *
     * @vars string
     */
    private $lastName;

    /**
     * Отчество
     * (64 символа)
     *
     * @var string
     */
    private $middleName;

    /**
     * Телефон
     * (64 символа)
     *
     * @var string
     */
    private $phone;

    /**
     * Адрес
     * (128 символов)
     *
     * @var string
     */
    private $address;

    /**
     * Название страны Покупателя
     * (64 символа)
     *
     * @var string
     */
    private $country;

    /**
     * Код штата/региона
     * (3 символа)
     *
     * @var string
     */
    private $state;

    /**
     * Город
     * (64 символа)
     *
     * @var string
     */
    private $city;

    /**
     * Почтовый индекс
     *
     * (64 символа)
     *
     * @var string
     */
    private $zip;

    /**
     * Верифицированный мерчантом номер телефона. Если передаётся, то значение
     * Phone устанавливается равным PhoneVerified.
     *
     * @var string
     */
    private $phoneVerified;
    /**
     * Номер телефона, для которого производится пополнение баланса. Если в свойствах точки продажи включён параметр
     * «Телекоммуникационные услуги» и мерчаном этот параметр не передан, то на странице оплаты отображается
     * обязательное поле для указания номера телефона.
     *
     * (формат телефонного номера с кодом страны: +7XXXXXXXXXX)
     *
     * @var string
     */
    private $destPhoneNum;

    /**
     * Внешний номер заказа мерчанта.
     * (строка в кодировке UTF-8, не содержащая символов ";", "=").
     *
     * @var string
     */
    private $merchantOrderId;

    /**
     * Разрешенные типы платежей.
     * JSON-объект формата:
     * {тип_платежа1:[ сумма_заказа, сумма_заказа ]…}
     *
     * @see \Tmconsulting\Uniteller\Payment\PaymentType
     *
     * @var string
     */
    private $paymentTypeLimits;

    /**
     * Адрес для возврата Плательщика после оплаты через СБП или SberPay в банковском приложении.
     * Строка до 255 символов.
     * Если не задано, для возврата будет выполнен редирект на страницу чека Uniteller.
     *
     * @var string
     */
    private $backUrl;

    /**
     * Ссылка на мобильное приложение Клиента для возврата после оплаты через СБП или SberPay.
     * Строка до 255 символов.
     * Если не задано, возврат будет выполнен по ссылке из параметра BackUrl
     *
     * @var string
     */
    private $deepLink;

    /**
     * Номер кошелька получателя электронных денежных средств. Если в свойствах точки продажи включён
     * параметр «Квази-кэш» и мерчаном этот параметр не передан, то на странице оплаты отображается обязательное
     * поле для указания номер кошелька.
     * (Латинские буквы и цифры)
     *
     * @var string
     */
    private $eWallet;

    /**
     * Пароль. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    private $password;

    /**
     * @param string $shopIdp
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setShopIdp(string $shopIdp): PaymentBuilder
    {
        $this->shopIdp = $shopIdp;

        return $this;
    }

    /**
     * @param string|int $orderIdp Номер заказа в системе интернет-магазина
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setOrderIdp($orderIdp): PaymentBuilder
    {
        $orderIdp = (string)$orderIdp;
        if (mb_strlen($orderIdp) > 127) {
            throw new NotValidParameterException('Not valid parameter ' . NameFieldsUniteller::ORDER_IDP . ', max 127');
        }
        $this->orderIdp = $orderIdp;

        return $this;
    }

    /**
     * @param int|float|string $subtotalP
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setSubtotalP($subtotalP): PaymentBuilder
    {
        $this->subtotalP = (float)$subtotalP;

        return $this;
    }

    /**
     * @param string $urlReturnOk
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setUrlReturnOk(string $urlReturnOk): PaymentBuilder
    {
        $this->urlReturnOk = $urlReturnOk;

        return $this;
    }

    /**
     * @param string $urlReturnNo
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setUrlReturnNo(string $urlReturnNo): PaymentBuilder
    {
        $this->urlReturnNo = $urlReturnNo;

        return $this;
    }

    /**
     * @param string $currency
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setCurrency(string $currency): PaymentBuilder
    {
        $currencies = Currency::toArray();
        if (!in_array($currency, $currencies, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::CURRENCY . ', must be one of the values: ' . implode(',', $currencies)
            );
        }
        $this->currency = $currency;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setEmail(string $email): PaymentBuilder
    {
        if (mb_strlen($email) > 64) {
            throw new NotValidParameterException('Not valid parameter ' . NameFieldsUniteller::EMAIL . ', max 64');
        }
        $this->email = $email;

        return $this;
    }

    /**
     * @param int $lifetime Время жизни формы оплаты в секундах, начиная с момента её показа
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setLifetime(int $lifetime): PaymentBuilder
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @param int $orderLifetime Время жизни заказа в секундах
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setOrderLifetime(int $orderLifetime): PaymentBuilder
    {
        $this->orderLifetime = $orderLifetime;

        return $this;
    }

    /**
     * @param string|int $customerIdp Идентификатор Покупателя
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setCustomerIdp($customerIdp): PaymentBuilder
    {
        $customerIdp = (string)$customerIdp;
        if (mb_strlen($customerIdp) > 64) {
            throw new NotValidParameterException('Not valid parameter ' . NameFieldsUniteller::CUSTOMER_IDP . ', max 64');
        }
        $this->customerIdp = $customerIdp;

        return $this;
    }

    /**
     * @param string $cardIdp Идентификатор зарегистрированной карты
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setCardIdp(string $cardIdp): PaymentBuilder
    {
        if (mb_strlen($cardIdp) > 128) {
            throw new NotValidParameterException('Not valid parameter ' . NameFieldsUniteller::CARD_IDP . ', max 128');
        }
        $this->cardIdp = $cardIdp;

        return $this;
    }

    /**
     * @param string $iData
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     * @deprecated
     *
     */
    public function setIData(string $iData): PaymentBuilder
    {
        $this->iData = $iData;

        return $this;
    }

    /**
     * @param string $ptCode Тип платежа. Произвольная строка длиной до десяти символов включительно.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setPtCode(string $ptCode): PaymentBuilder
    {
        $this->ptCode = $ptCode;

        return $this;
    }

    /**
     * @param int $meanType
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setMeanType(int $meanType): PaymentBuilder
    {
        $types = MeanType::toArray();
        if (!in_array($meanType, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::MEAN_TYPE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->meanType = $meanType;

        return $this;
    }

    /**
     * @param int $eMoneyType
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setEMoneyType(int $eMoneyType): PaymentBuilder
    {
        $types = EMoneyType::toArray();
        if (!in_array($eMoneyType, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::E_MONEY_TYPE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->eMoneyType = $eMoneyType;

        return $this;
    }

    /**
     * @param int $billLifetime Срок жизни заказа оплаты в часах
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setBillLifetime(int $billLifetime): PaymentBuilder
    {
        $this->billLifetime = $billLifetime;

        return $this;
    }

    /**
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function usePreAuth(): PaymentBuilder
    {
        $this->preAuth = true;

        return $this;
    }

    /**
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function useRecurrentPayment(): PaymentBuilder
    {
        $this->isRecurrentStart = true;

        return $this;
    }

    /**
     * @param array $callbackFields
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setCallbackFields(array $callbackFields): PaymentBuilder
    {
        $this->callbackFields = join(' ', $callbackFields);

        return $this;
    }

    /**
     * @param string $callbackFormat
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setCallbackFormat(string $callbackFormat): PaymentBuilder
    {
        $this->callbackFormat = $callbackFormat;

        return $this;
    }

    /**
     * @param string $language
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setLanguage(string $language): PaymentBuilder
    {
        $types = Language::toArray();
        if (!in_array($language, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::LANGUAGE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->language = $language;

        return $this;
    }

    /**
     * @param string $comment Комментарий к платежу.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setComment(string $comment): PaymentBuilder
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @param string $firstName
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setFirstName(string $firstName): PaymentBuilder
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setLastName(string $lastName): PaymentBuilder
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $middleName
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setMiddleName(string $middleName): PaymentBuilder
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @param string $phone
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setPhone(string $phone): PaymentBuilder
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string $address
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setAddress(string $address): PaymentBuilder
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param string $country
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setCountry(string $country): PaymentBuilder
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param string $state
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setState(string $state): PaymentBuilder
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @param string $city
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setCity(string $city): PaymentBuilder
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @param string|int $zip Почтовый индекс
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setZip($zip): PaymentBuilder
    {
        $this->zip = (string)$zip;

        return $this;
    }

    /**
     * @param string $phone Верифицированный номер телефона
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setPhoneVerified(string $phone): PaymentBuilder
    {
        $this->phoneVerified = $phone;

        return $this;
    }

    /**
     * @param string $phone Номер телефона, для которого производится пополнение баланса.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setDestPhoneNum(string $phone): PaymentBuilder
    {
        $this->destPhoneNum = $phone;

        return $this;
    }

    /**
     * @param string|int $merchantOrderId
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setMerchantOrderId($merchantOrderId): PaymentBuilder
    {
        $this->merchantOrderId = (string)$merchantOrderId;

        return $this;
    }

    /**
     * @param string|array $paymentTypeLimits
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setPaymentTypeLimits($paymentTypeLimits): PaymentBuilder
    {
        if (is_array($paymentTypeLimits)) {
            $this->paymentTypeLimits = json_encode($paymentTypeLimits);
        } else {
            $this->paymentTypeLimits = (string)$paymentTypeLimits;
        }

        return $this;
    }

    /**
     * @param string $backUrl Адрес для возврата Плательщика после оплаты через СБП или SberPay в банковском приложении.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setBackUrl(string $backUrl): PaymentBuilder
    {
        $this->backUrl = $backUrl;

        return $this;
    }

    /**
     * @param string $deepLink Ссылка на мобильное приложение Клиента для возврата после оплаты через СБП или SberPay.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setDeepLink(string $deepLink): PaymentBuilder
    {
        $this->deepLink = $deepLink;

        return $this;
    }

    /**
     * @param string $eWallet Номер кошелька получателя электронных денежных средств.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setEWallet(string $eWallet): PaymentBuilder
    {
        $this->eWallet = $eWallet;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     */
    public function setPassword(string $password): PaymentBuilder
    {
        $this->password = $password;

        return $this;
    }

    /* Getters */

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getShopIdp(): string
    {
        if (empty($this->shopIdp)) {
            throw new RequiredParameterException(NameFieldsUniteller::SHOP_IDP);
        }

        return $this->shopIdp;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getOrderIdp(): string
    {
        if ($this->orderIdp === '') {
            throw new RequiredParameterException(NameFieldsUniteller::ORDER_IDP);
        }
        return $this->orderIdp;
    }

    /**
     * @return float
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getSubtotalP(): float
    {
        if ($this->subtotalP <= 0) {
            throw new RequiredParameterException(NameFieldsUniteller::SUBTOTAL_P);
        }
        return $this->subtotalP;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getUrlReturnOk(): string
    {
        if (empty($this->urlReturnOk)) {
            throw new RequiredParameterException(NameFieldsUniteller::URL_RETURN_OK);
        }
        return $this->urlReturnOk;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getUrlReturnNo(): string
    {
        if (empty($this->urlReturnNo)) {
            throw new RequiredParameterException(NameFieldsUniteller::URL_RETURN_NO);
        }
        return $this->urlReturnNo;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return int|null
     */
    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }

    /**
     * @return int|null
     */
    public function getOrderLifetime(): ?int
    {
        return $this->orderLifetime;
    }

    /**
     * @return string|null
     */
    public function getCustomerIdp(): ?string
    {
        return $this->customerIdp;
    }

    /**
     * @return string|null
     */
    public function getCardIdp(): ?string
    {
        return $this->cardIdp;
    }

    /**
     * @return string|null
     * @deprecated
     *
     */
    public function getIData(): ?string
    {
        return $this->iData;
    }

    /**
     * @return string|null
     */
    public function getPtCode(): ?string
    {
        return $this->ptCode;
    }

    /**
     * @return int|null
     */
    public function getMeanType(): ?int
    {
        return $this->meanType;
    }

    /**
     * @return int|null
     */
    public function getEMoneyType(): ?int
    {
        return $this->eMoneyType;
    }

    /**
     * @return int|null
     */
    public function getBillLifetime(): ?int
    {
        return $this->billLifetime;
    }

    /**
     * @return string|null
     */
    public function isPreAuth(): ?string
    {
        if ($this->preAuth) {
            return '1';
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function isIsRecurrentStart(): ?string
    {
        if ($this->isRecurrentStart) {
            return '1';
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getCallbackFields(): ?string
    {
        return $this->callbackFields;
    }

    /**
     * @return string|null
     */
    public function getCallbackFormat(): ?string
    {
        return $this->callbackFormat;
    }

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @return string|null
     */
    public function getPhoneVerified(): ?string
    {
        return $this->phoneVerified;
    }

    /**
     * @return string|null
     */
    public function getDestPhoneNum(): ?string
    {
        return $this->destPhoneNum;
    }

    /**
     * @return string|null
     */
    public function getMerchantOrderId(): ?string
    {
        return $this->merchantOrderId;
    }

    /**
     * @return string|null
     */
    public function getPaymentTypeLimits(): ?string
    {
        return $this->paymentTypeLimits;
    }

    /**
     * @return string|null
     */
    public function getBackUrl(): ?string
    {
        return $this->backUrl;
    }

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string
    {
        return $this->deepLink;
    }

    /**
     * @return string|null
     */
    public function getEWallet(): ?string
    {
        return $this->eWallet;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getPassword(): string
    {
        if (empty($this->password)) {
            throw new RequiredParameterException(NameFieldsUniteller::PASSWORD);
        }
        return $this->password;
    }

    /**
     * Возвращает массив со значениями параметров. Порядок следования полей должен соответствовать
     * порядку полей в signature, поэтому поля выстроены в определенном порядке.
     *
     * @return array
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function toArray(): array
    {
        $arr = [
            NameFieldsUniteller::SHOP_IDP   => $this->getShopIdp(),
            NameFieldsUniteller::ORDER_IDP  => $this->getOrderIdp(),
            NameFieldsUniteller::SUBTOTAL_P => $this->getSubtotalP(),
        ];
        if ($this->getMeanType() !== null) {
            $arr[NameFieldsUniteller::MEAN_TYPE] = $this->getMeanType();
        }
        if ($this->getEMoneyType() !== null) {
            $arr[NameFieldsUniteller::E_MONEY_TYPE] = $this->getEMoneyType();
        }
        if ($this->getLifetime() !== null) {
            $arr[NameFieldsUniteller::LIFETIME] = $this->getLifetime();
        }
        if ($this->getCustomerIdp() !== null) {
            $arr[NameFieldsUniteller::CUSTOMER_IDP] = $this->getCustomerIdp();
        }
        if ($this->getCardIdp() !== null) {
            $arr[NameFieldsUniteller::CARD_IDP] = $this->getCardIdp();
        }
        if ($this->getIData() !== null) {
            $arr[NameFieldsUniteller::I_DATA] = $this->getIData();
        }
        if ($this->getPtCode() !== null) {
            $arr[NameFieldsUniteller::PT_CODE] = $this->getPtCode();
        }
        if ($this->getOrderLifetime() !== null) {
            $arr[NameFieldsUniteller::ORDER_LIFETIME] = $this->getOrderLifetime();
        }
        if ($this->getPhoneVerified() !== null) {
            $arr[NameFieldsUniteller::PHONE_VERIFIED] = $this->getPhoneVerified();
        }
        if ($this->getMerchantOrderId() !== null) {
            $arr[NameFieldsUniteller::MERCHANT_ORDER_ID] = $this->getMerchantOrderId();
        }
        if ($this->getPaymentTypeLimits() !== null) {
            $arr[NameFieldsUniteller::PAYMENT_TYPE_LIMITS] = $this->getPaymentTypeLimits();
        }
        /**
         * Поля выше участвуют в расчете signature, ниже нет
         */
        if ($this->getCallbackFields() !== null) {
            $arr[NameFieldsUniteller::CALLBACK_FIELDS] = $this->getCallbackFields();
        }
        if ($this->getCurrency() !== null) {
            $arr[NameFieldsUniteller::CURRENCY] = $this->getCurrency();
        }
        if ($this->getEmail() !== null) {
            $arr[NameFieldsUniteller::EMAIL] = $this->getEmail();
        }
        if ($this->getBillLifetime() !== null) {
            $arr[NameFieldsUniteller::BILL_LIFETIME] = $this->getBillLifetime();
        }
        if ($this->isPreAuth() !== null) {
            $arr[NameFieldsUniteller::PREAUTH] = $this->isPreAuth();
        }
        if ($this->isIsRecurrentStart() !== null) {
            $arr[NameFieldsUniteller::IS_RECURRENT_START] = $this->isIsRecurrentStart();
        }
        if ($this->getCallbackFormat() !== null) {
            $arr[NameFieldsUniteller::CALLBACK_FORMAT] = $this->getCallbackFormat();
        }
        if ($this->getBackUrl() !== null) {
            $arr[NameFieldsUniteller::BACK_URL] = $this->getBackUrl();
        }
        if ($this->getDeepLink() !== null) {
            $arr[NameFieldsUniteller::DEEP_LINK] = $this->getDeepLink();
        }
        if ($this->getLanguage() !== null) {
            $arr[NameFieldsUniteller::LANGUAGE] = $this->getLanguage();
        }
        if ($this->getEWallet() !== null) {
            $arr[NameFieldsUniteller::E_WALLET] = $this->getEWallet();
        }
        if ($this->getDestPhoneNum() !== null) {
            $arr[NameFieldsUniteller::DEST_PHONE_NUM] = $this->getDestPhoneNum();
        }
        if ($this->getComment() !== null) {
            $arr[NameFieldsUniteller::COMMENT] = $this->getComment();
        }
        if ($this->getFirstName() !== null) {
            $arr[NameFieldsUniteller::FIRST_NAME] = $this->getFirstName();
        }
        if ($this->getLastName() !== null) {
            $arr[NameFieldsUniteller::LAST_NAME] = $this->getLastName();
        }
        if ($this->getMiddleName() !== null) {
            $arr[NameFieldsUniteller::MIDDLE_NAME] = $this->getMiddleName();
        }
        if ($this->getPhone() !== null) {
            $arr[NameFieldsUniteller::PHONE] = $this->getPhone();
        }
        // сли есть верифицированный телефон, то чтобы не было указано в Phone, оно заменяется на PhoneVerified
        if ($this->getPhoneVerified() !== null) {
            $arr[NameFieldsUniteller::PHONE] = $this->getPhoneVerified();
        }
        if ($this->getAddress() !== null) {
            $arr[NameFieldsUniteller::ADDRESS] = $this->getAddress();
        }
        if ($this->getCountry() !== null) {
            $arr[NameFieldsUniteller::COUNTRY] = $this->getCountry();
        }
        if ($this->getState() !== null) {
            $arr[NameFieldsUniteller::STATE] = $this->getState();
        }
        if ($this->getCity() !== null) {
            $arr[NameFieldsUniteller::CITY] = $this->getCity();
        }
        if ($this->getZip() !== null) {
            $arr[NameFieldsUniteller::ZIP] = $this->getZip();
        }
        $arr[NameFieldsUniteller::URL_RETURN_OK] = $this->getUrlReturnOk();
        $arr[NameFieldsUniteller::URL_RETURN_NO] = $this->getUrlReturnNo();

        return $arr;
    }
}
