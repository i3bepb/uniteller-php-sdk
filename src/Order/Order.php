<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

namespace Tmconsulting\Uniteller\Order;

use DateTime;
use Tmconsulting\Uniteller\Error\ResponseCode;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;

/**
 * Class Order
 */
class Order implements \JsonSerializable
{
    /**
     * Адрес Держателя карты
     *
     * @var string|null
     */
    protected $address = null;

    /**
     * Код подтверждения транзакции от процессингового центра
     *
     * @var string|null
     */
    protected $approvalCode = null;

    /**
     * Имя банка-эмитента
     *
     * @var string|null
     */
    protected $bankName = null;

    /**
     * Номер платежа в системе Uniteller (RRN).
     *
     * @var string|null
     */
    protected $billNumber = null;

    /**
     * Идентификатор (логин) Мерчанта в сервисе Booking.com
     *
     * @var string|null
     */
    protected $bookingcomId = null;

    /**
     * Пароль Мерчанта в сервисе Booking.com
     *
     * @var string|null
     */
    protected $bookingcomPincode = null;

    /**
     * Идентификатор зарегистрированной карты
     *
     * @var string|null
     */
    protected $cardIdp = null;

    /**
     * Информация, введённая Покупателем на странице оплаты в поле имени владельца карты.
     *
     * @var string|null
     */
    protected $cardHolder = null;

    /**
     * Первые 6 цифр и последние 4 цифры номера карты (PAN), соединённые звёздочками
     *
     * @var string|null
     */
    protected $cardNumber = null;

    /**
     * Тип платёжной системы карты (возможные значения: visa, mastercard,  dinnersclub, jcb)
     *
     * @var string|null
     */
    protected $cardType = null;

    /**
     * Комментарий к оплате (передаётся в запросе на оплату)
     *
     * @var string|null
     */
    protected $comment = null;

    /**
     * Код валюты
     *
     * @var string|null
     */
    protected $currency = null;

    /**
     * Наличие CVC2/CVV2/4DBC
     * (0 — авторизация без CVC2, 1 — авторизация с СVC2)
     *
     * @var int|null
     */
    protected $cvc2 = null;

    /**
     * Дата и время создания заказа в системе
     * Client в формате dd.mm.yyyy hh:mm:ss
     *
     * @var DateTime|null
     */
    protected $date = null;

    /**
     * Адрес электронной почты Держателя карты
     *
     * @var string|null
     */
    protected $email = null;

    /**
     * Тип электронной валюты
     *
     * @var string|null
     */
    protected $eMoneyType = null;

    /**
     * Данные заказа, выставленного в электронной платёжной системе.
     *
     * @var array|null
     */
    protected $eOrderData = null;

    /**
     * Код ответа процессингового центра
     *
     * @var int|null
     */
    protected $errorCode = null;

    /**
     * Расшифровка кода ответа процессингового центра
     *
     * @var string|null
     */
    protected $errorComment = null;

    /**
     * Имя Держателя карты
     *
     * @var string|null
     */
    protected $firstName = null;

    /**
     * @var int|null
     */
    protected $gdsPaymentPurposeId = null;

    /**
     * «Длинная запись» (параметр, включающий дополнительную информацию, необходимую при бронировании и оплате авиабилетов)
     *
     * @var string|null
     */
    protected $iData = null;

    /**
     * IP-адрес Покупателя
     *
     * @var string|null
     */
    protected $ip = null;

    /**
     * Фамилия Держателя карты
     *
     * @var string|null
     */
    protected $lastName = null;

    /**
     * Идентификатор кредитной организации
     *
     * @var string|null
     */
    protected $loanId = null;

    /**
     * Сообщение об ошибке (текст ошибки, если она произошла)
     *
     * @var string|null
     */
    protected $message = null;

    /**
     * Отчество Держателя карты
     *
     * @var string|null
     */
    protected $middleName = null;

    /**
     * Признак необходимости подтверждения преавторизации
     *
     * «0» — платёж без преавторизации или уже подтверждён;
     * «1» — необходимо подтверждение.
     *
     * @var int|null
     */
    protected $needConfirm = null;

    /**
     * Номер заказа в интернет-магазине Мерчанта
     *
     * @var string|null
     */
    protected $orderNumber = null;

    /**
     * Идентификатор «родительского» платежа (значение параметра OrderNumber) для рекуррентного платежа.
     * Пустое значение, если платёж нерекуррентный.
     *
     * @var string|null
     */
    protected $parentOrderNumber = null;

    /**
     * «1» — оплата кредитной картой;
     * «3» — оплата с помощью электронной валюты.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentType
     *
     * @var int|null
     */
    protected $paymentType = null;

    /**
     * Телефон Держателя карты
     *
     * @var string|null
     */
    protected $phone = null;

    /**
     * Тип платежа
     *
     * @var string|null
     */
    protected $ptCode = null;

    /**
     * Идентификатор QR-кода, выданный НСПК
     *
     * @var string|null
     */
    protected $qrcId = null;
    /**
     * Идентификатор сохранённого токена СБП (идентификатор привязанного в СБП счёта, будет пустым, если Плательщик
     * не привязывал счёт).
     *
     * @var string|null
     */
    protected $tokenIdp = null;

    /**
     * Описание чека фискализации.
     *
     * @var \Tmconsulting\Uniteller\Receipt\FiscalReceipt[]|null
     */
    protected $receipts = null;

    /**
     * Расшифровка кода возврата
     *
     * @var string|null
     */
    protected $recommendation = null;

    /**
     * Код возврата
     *
     * @var string|null
     */
    protected $responseCode = null;

    /**
     * Состояние заказа
     * Order\Status::PAID
     *
     * @var string|null
     */
    protected $status = null;

    /**
     * Сумма всех средств, уплаченных по одному заказу.
     * Десятичный разделитель — точка
     *
     * @var string|null
     */
    protected $total = null;

    /**
     * @var DateTime|null
     */
    protected $packetDate = null;

    /**
     * @var string|null
     */
    protected $country = null;

    /**
     * @var string|null
     */
    protected $rate = null;

    /**
     * @var string|null
     */
    protected $cardSubType = null;

    /**
     * @var string|null
     */
    protected $protocolTypeName = null;

    /**
     * @var string|null
     */
    protected $processingName = null;

    /**
     * @var string|null
     */
    protected $acquirerID = null;

    /**
     * @var bool|null
     */
    protected $isOtherCard = null;

    /**
     * Сумма заказа.
     *
     * @var string|null
     */
    protected $sum = null;

    /**
     * Уникальный номер заказа в Платёжном шлюзе
     *
     * @var string|null
     */
    protected $sberOrderId = null;

    /**
     * @var string|null
     */
    protected $giftCert = null;

    /**
     * @var string|null
     */
    protected $opkcID = null;

    /**
     * @var string|null
     */
    protected $tpayRequestId = null;

    /**
     * @var string|null
     */
    protected $bnplRequestId = null;

    /**
     * @param string|null $address
     *
     * @return $this
     */
    public function setAddress(?string $address): Order
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param string|null $approvalCode
     *
     * @return $this
     */
    public function setApprovalCode(?string $approvalCode): Order
    {
        $this->approvalCode = $approvalCode;

        return $this;
    }

    /**
     * @param string|null $bankName
     *
     * @return $this
     */
    public function setBankName(?string $bankName): Order
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * @param string|null $billNumber
     *
     * @return $this
     */
    public function setBillNumber(?string $billNumber): Order
    {
        $this->billNumber = $billNumber;

        return $this;
    }

    /**
     * @param string|null $bookingcomId
     *
     * @return $this
     */
    public function setBookingcomId(?string $bookingcomId): Order
    {
        $this->bookingcomId = $bookingcomId;

        return $this;
    }

    /**
     * @param string|null $bookingcomPincode
     *
     * @return $this
     */
    public function setBookingcomPincode(?string $bookingcomPincode): Order
    {
        $this->bookingcomPincode = $bookingcomPincode;

        return $this;
    }

    /**
     * @param string|null $cardIdp
     *
     * @return $this
     */
    public function setCardIdp(?string $cardIdp): Order
    {
        $this->cardIdp = $cardIdp;

        return $this;
    }

    /**
     * @param string|null $cardHolder
     *
     * @return $this
     */
    public function setCardHolder(?string $cardHolder): Order
    {
        $this->cardHolder = $cardHolder;

        return $this;
    }

    /**
     * @param string|null $cardNumber
     *
     * @return $this
     */
    public function setCardNumber(?string $cardNumber): Order
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    /**
     * @param string|null $cardType
     *
     * @return $this
     */
    public function setCardType(?string $cardType): Order
    {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * @param string|null $comment
     *
     * @return $this
     */
    public function setComment(?string $comment): Order
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @param string|null $currency
     *
     * @return $this
     */
    public function setCurrency(?string $currency): Order
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @param int|null $value
     *
     * @return $this
     */
    public function setCvc2(?int $value): Order
    {
        $this->cvc2 = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function withCvc2(): Order
    {
        $this->cvc2 = 1;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutCvc2(): Order
    {
        $this->cvc2 = 0;

        return $this;
    }

    /**
     * @param string|null $date
     *
     * @return $this
     */
    public function setDate(?string $date): Order
    {
        if (empty($date)) {
            return $this;
        }

        /**
         * Ёбаный насрать, а даты зачем разного формата отдавать?
         * Полностью разделяю и поддерживаю данное негодование, значит ебашим костыли))
         *
         * Например, если ответ запрашивается в формате CSV, то дата в формате d.m.Y H:i:s,
         * а если в формате XML, то дата в формате Y-m-d H:i:s
         */
        $date = str_replace('.', '-', $date);
        $this->date = DateTime::createFromFormat('U', strtotime($date));

        return $this;
    }

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email): Order
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param string|null $eMoneyType
     *
     * @return $this
     */
    public function setEMoneyType(?string $eMoneyType): Order
    {
        $this->eMoneyType = $eMoneyType;

        return $this;
    }

    /**
     * @param string|null $eOrderData
     *
     * @return $this
     */
    public function setEOrderData(?string $eOrderData): Order
    {
        if (empty($eOrderData)) {
            return $this;
        }

        $arr = [];
        foreach (explode(', ', $eOrderData) as $item) {
            list($key, $value) = explode('=', $item, 2);
            $arr[$key] = $value;
        }
        $this->eOrderData = $arr;

        return $this;
    }

    /**
     * @param int|null $errorCode
     *
     * @return $this
     */
    public function setErrorCode(?int $errorCode): Order
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @param string|null $errorComment
     *
     * @return $this
     */
    public function setErrorComment(?string $errorComment): Order
    {
        $this->errorComment = $errorComment;

        return $this;
    }

    /**
     * @param string|null $firstName
     *
     * @return $this
     */
    public function setFirstName(?string $firstName): Order
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param int|null $gdsPaymentPurposeId
     *
     * @return $this
     */
    public function setGdsPaymentPurposeId(?int $gdsPaymentPurposeId): Order
    {
        $this->gdsPaymentPurposeId = $gdsPaymentPurposeId;

        return $this;
    }

    /**
     * @param string|null $iData
     *
     * @return $this
     */
    public function setIData(?string $iData): Order
    {
        $this->iData = $iData;

        return $this;
    }

    /**
     * @param string|null $ip
     *
     * @return $this
     */
    public function setIp(?string $ip): Order
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @param string|null $lastName
     *
     * @return $this
     */
    public function setLastName(?string $lastName): Order
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string|null $loanId
     *
     * @return $this
     */
    public function setLoanId(?string $loanId): Order
    {
        $this->loanId = $loanId;

        return $this;
    }

    /**
     * @param string|null $message
     *
     * @return $this
     */
    public function setMessage(?string $message): Order
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string|null $middleName
     *
     * @return $this
     */
    public function setMiddleName(?string $middleName): Order
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @param int|null $needConfirm
     *
     * @return $this
     */
    public function setNeedConfirm(?int $needConfirm): Order
    {
        $this->needConfirm = $needConfirm;

        return $this;
    }

    /**
     * @param string|null $orderNumber
     *
     * @return $this
     */
    public function setOrderNumber(?string $orderNumber): Order
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * @param string|null $parentOrderNumber
     *
     * @return $this
     */
    public function setParentOrderNumber(?string $parentOrderNumber): Order
    {
        $this->parentOrderNumber = $parentOrderNumber;

        return $this;
    }

    /**
     * @param int|null $paymentType
     *
     * @return $this
     */
    public function setPaymentType(?int $paymentType): Order
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @param string|null $phone
     *
     * @return $this
     */
    public function setPhone(?string $phone): Order
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @param string|null $ptCode
     *
     * @return $this
     */
    public function setPtCode(?string $ptCode): Order
    {
        $this->ptCode = $ptCode;

        return $this;
    }

    /**
     * @param string|null $recommendation
     *
     * @return $this
     */
    public function setRecommendation(?string $recommendation): Order
    {
        $this->recommendation = $recommendation;

        return $this;
    }

    /**
     * @param string|null $responseCode
     *
     * @return $this
     */
    public function setResponseCode(?string $responseCode): Order
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * @param string|null $status
     *
     * @return $this
     */
    public function setStatus(?string $status): Order
    {
        $this->status = Status::resolve($status);

        return $this;
    }

    /**
     * @param string|null $total
     *
     * @return $this
     */
    public function setTotal(?string $total): Order
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return string|null Адрес Держателя карты.
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string|null Код подтверждения транзакции от процессингового центра.
     */
    public function getApprovalCode(): ?string
    {
        return $this->approvalCode;
    }

    /**
     * @return string|null Имя банка-эмитента.
     */
    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    /**
     * @return string|null Номер платежа в системе Uniteller.
     */
    public function getBillNumber(): ?string
    {
        return $this->billNumber;
    }

    /**
     * @return string|null Идентификатор (логин) Мерчанта в сервисе Booking.com.
     */
    public function getBookingcomId(): ?string
    {
        return $this->bookingcomId;
    }

    /**
     * @return string|null Пароль Мерчанта в сервисе Booking.com.
     */
    public function getBookingcomPincode(): ?string
    {
        return $this->bookingcomPincode;
    }

    /**
     * @return string|null Идентификатор зарегистрированной карты.
     */
    public function getCardIdp(): ?string
    {
        return $this->cardIdp;
    }

    /**
     * @return string|null Информация, введённая Покупателем на странице оплаты в поле имени владельца карты.
     */
    public function getCardHolder(): ?string
    {
        return $this->cardHolder;
    }

    /**
     * @return string|null Первые 6 цифр и последние 4 цифры номера карты (PAN), соединённые звёздочками.
     */
    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    /**
     * @return string|null Тип платёжной системы карты (возможные значения: visa, mastercard, dinnersclub, jcb, mir).
     */
    public function getCardType(): ?string
    {
        return $this->cardType;
    }

    /**
     * @return string|null Комментарий к оплате.
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return string|null Код валюты.
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return bool|null Наличие CVC2/CVV2/4DBC (false — авторизация без CVC2, true — авторизация с СVC2).
     */
    public function isCvc2(): ?bool
    {
        if ($this->cvc2 === 1) {
            return true;
        }
        if ($this->cvc2 === 0) {
            return false;
        }
        return null;
    }

    /**
     * @return int|null Наличие CVC2/CVV2/4DBC (0 — авторизация без CVC2, 1 — авторизация с СVC2).
     */
    public function getCvc2(): ?int
    {
        return $this->cvc2;
    }

    /**
     * @return DateTime|null Дата и время создания заказа в системе Uniteller.
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return string|null Адрес электронной почты Держателя карты.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null Тип электронной валюты.
     */
    public function getEMoneyType(): ?string
    {
        return $this->eMoneyType;
    }

    /**
     * @return array|null Данные заказа, выставленного в электронной платёжной системе. В формате «title1=value1, title2=value2, …».
     */
    public function getEOrderData(): ?array
    {
        return $this->eOrderData;
    }

    /**
     * @return int|null Код ответа процессингового центра.
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * @return string|null Расшифровка кода ответа процессингового центра.
     */
    public function getErrorComment(): ?string
    {
        return $this->errorComment;
    }

    /**
     * @return string|null Имя Держателя карты.
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return int|null Назначение платежа через ГДС
     *             «10» — оплата комиссионного вознаграждения Агентства;
     *             «20» — оплата билетов;
     *             для платежей без участия ГДС этот параметр пустой. В ответе в формате SOAP на запрос результата
     *             авторизации в текущей версии сервиса этот параметр не возвращается.
     */
    public function getGdsPaymentPurposeId(): ?int
    {
        return $this->gdsPaymentPurposeId;
    }

    /**
     * @return string|null «Длинная запись» (параметр, включающий дополнительную информацию, необходимую
     *                     при бронировании и оплате авиабилетов).
     */
    public function getIData(): ?string
    {
        return $this->iData;
    }

    /**
     * @return string|null IP-адрес Покупателя.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @return string|null Фамилия Держателя карты.
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string|null Идентификатор кредитной организации.
     */
    public function getLoanId(): ?string
    {
        return $this->loanId;
    }

    /**
     * @return string|null Сообщение об ошибке (текст ошибки, если она произошла).
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return string|null Отчество Держателя карты.
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @return bool|null Признак необходимости подтверждения преавторизации.
     *              «0» — платёж без преавторизации или уже подтверждён;
     *              «1» — необходимо подтверждение.
     */
    public function isNeedConfirm(): ?bool
    {
        if ($this->needConfirm === 1) {
            return true;
        }
        if ($this->needConfirm === 0) {
            return false;
        }
        return null;
    }

    /**
     * @return int|null Признак необходимости подтверждения преавторизации.
     *                  0 — платёж без преавторизации или уже подтверждён;
     *                  1 — необходимо подтверждение.
     */
    public function getNeedConfirm(): ?int
    {
        return $this->needConfirm;
    }

    /**
     * @return string|null Номер заказа в интернет-магазине Мерчанта.
     */
    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    /**
     * @return string|null Идентификатор «родительского» платежа для рекуррентного платежа.
     *                Пустое значение, если платёж не рекуррентный.
     */
    public function getParentOrderNumber(): ?string
    {
        return $this->parentOrderNumber;
    }

    /**
     * @return int|null
     *  «1» — оплата кредитной картой;
     *  «3» — оплата с помощью электронной валюты;
     *  «13» — оплата через СБП;
     *  «14» – оплата через SberPay.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentType
     */
    public function getPaymentType(): ?int
    {
        return $this->paymentType;
    }

    /**
     * @return string|null Телефон Держателя карты.
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null Тип платежа.
     */
    public function getPtCode(): ?string
    {
        return $this->ptCode;
    }

    /**
     * @return string|null Расшифровка кода возврата.
     */
    public function getRecommendation(): ?string
    {
        return $this->recommendation;
    }

    /**
     * @return string|null Код возврата.
     */
    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return ResponseCode::message($this->responseCode);
    }

    /**
     * @return string|null Состояние заказа.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null Сумма всех средств, уплаченных по одному заказу.
     */
    public function getTotal(): ?string
    {
        return $this->total;
    }

    /**
     * @param string|null $packetDate
     *
     * @return $this
     */
    public function setPacketDate(?string $packetDate): Order
    {
        if (empty($packetDate)) {
            return $this;
        }

        // 10.03.2017 15:42:42 - o_O
        // 2018.07.16 10:54:05 - O_o
        $packetDate = str_replace('.', '-', $packetDate);
        $this->packetDate = DateTime::createFromFormat('U', strtotime($packetDate));

        return $this;
    }

    /**
     * @return DateTime|null Дата операции.
     */
    public function getPacketDate(): ?DateTime
    {
        return $this->packetDate;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return $this
     */
    public function setCountry(?string $country): Order
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRate(): ?string
    {
        return $this->rate;
    }

    /**
     * @param string|null $rate
     *
     * @return $this
     */
    public function setRate(?string $rate): Order
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCardSubType(): ?string
    {
        return $this->cardSubType;
    }

    /**
     * @param string|null $cardSubType
     *
     * @return $this
     */
    public function setCardSubType(?string $cardSubType): Order
    {
        $this->cardSubType = $cardSubType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProtocolTypeName(): ?string
    {
        return $this->protocolTypeName;
    }

    /**
     * @param string|null $protocolTypeName
     *
     * @return $this
     */
    public function setProtocolTypeName(?string $protocolTypeName): Order
    {
        $this->protocolTypeName = $protocolTypeName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProcessingName(): ?string
    {
        return $this->processingName;
    }

    /**
     * @param string|null $processingName
     *
     * @return $this
     */
    public function setProcessingName(?string $processingName): Order
    {
        $this->processingName = $processingName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcquirerID(): ?string
    {
        return $this->acquirerID;
    }

    /**
     * @param string|null $acquirerID
     *
     * @return $this
     */
    public function setAcquirerID(?string $acquirerID): Order
    {
        $this->acquirerID = $acquirerID;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isOtherCard(): ?bool
    {
        return $this->isOtherCard;
    }

    /**
     * @param bool|null $isOtherCard
     *
     * @return $this
     */
    public function setIsOtherCard(?bool $isOtherCard): Order
    {
        $this->isOtherCard = $isOtherCard;

        return $this;
    }

    /**
     * @return string|null Идентификатор QR-кода, выданный НСПК.
     */
    public function getQrcId(): ?string
    {
        return $this->qrcId;
    }

    /**
     * @param string|null $qrcId
     *
     * @return $this
     */
    public function setQrcId(?string $qrcId): Order
    {
        $this->qrcId = $qrcId;

        return $this;
    }

    /**
     * @return \Tmconsulting\Uniteller\Receipt\FiscalReceipt[]|null Все чеки связанные с заказом.
     */
    public function getReceipts(): ?array
    {
        return $this->receipts;
    }

    /**
     * @param \Tmconsulting\Uniteller\Receipt\FiscalReceipt[]|null $receipts
     *
     * @return $this
     */
    public function setReceipts(?array $receipts): Order
    {
        $this->receipts = $receipts;

        return $this;
    }

    /**
     * @return string|null Уникальный номер заказа в Платёжном шлюзе.
     */
    public function getSberOrderId(): ?string
    {
        return $this->sberOrderId;
    }

    /**
     * @param string|null $sberOrderId Уникальный номер заказа в Платёжном шлюзе.
     *
     * @return $this
     */
    public function setSberOrderId(?string $sberOrderId): Order
    {
        $this->sberOrderId = $sberOrderId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSum(): ?string
    {
        return $this->sum;
    }

    /**
     * @param string|null $sum Сумма заказа.
     *
     * @return $this
     */
    public function setSum(?string $sum): Order
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * @return string|null Идентификатор сохранённого токена СБП.
     */
    public function getTokenIdp(): ?string
    {
        return $this->tokenIdp;
    }

    /**
     * @param string|null $tokenIdp Идентификатор сохранённого токена СБП.
     *
     * @return $this
     */
    public function setTokenIdp(?string $tokenIdp): Order
    {
        $this->tokenIdp = $tokenIdp;

        return $this;
    }

    public function getGiftCert(): ?string
    {
        return $this->giftCert;
    }

    /**
     * @param string|null $giftCert
     *
     * @return $this
     */
    public function setGiftCert(?string $giftCert): Order
    {
        $this->giftCert = $giftCert;

        return $this;
    }

    public function getOpkcID(): ?string
    {
        return $this->opkcID;
    }

    /**
     * @param string|null $opkcID
     *
     * @return $this
     */
    public function setOpkcID(?string $opkcID): Order
    {
        $this->opkcID = $opkcID;

        return $this;
    }

    public function getTpayRequestId(): ?string
    {
        return $this->tpayRequestId;
    }

    /**
     * @param string|null $tpayRequestId
     *
     * @return $this
     */
    public function setTpayRequestId(?string $tpayRequestId): Order
    {
        $this->tpayRequestId = $tpayRequestId;

        return $this;
    }

    public function getBnplRequestId(): ?string
    {
        return $this->bnplRequestId;
    }

    /**
     * @param string|null $bnplRequestId
     *
     * @return $this
     */
    public function setBnplRequestId(?string $bnplRequestId): Order
    {
        $this->bnplRequestId = $bnplRequestId;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            SFields::ADDRESS                => $this->getAddress(),
            SFields::APPROVAL_CODE          => $this->getApprovalCode(),
            SFields::BANK_NAME              => $this->getBankName(),
            SFields::BILL_NUMBER            => $this->getBillNumber(),
            SFields::BOOKINGCOM_ID          => $this->getBookingcomId(),
            SFields::BOOKINGCOM_PINCODE     => $this->getBookingcomPincode(),
            SFields::CARD_IDP               => $this->getCardIdp(),
            SFields::CARD_HOLDER            => $this->getCardHolder(),
            SFields::CARD_NUMBER            => $this->getCardNumber(),
            SFields::CARD_TYPE              => $this->getCardType(),
            SFields::COMMENT                => $this->getComment(),
            SFields::CURRENCY               => $this->getCurrency(),
            SFields::CVC2                   => $this->getCvc2(),
            SFields::DATE                   => $this->getDate(),
            SFields::PACKET_DATE            => $this->getPacketDate(),
            SFields::EMAIL                  => $this->getEmail(),
            SFields::E_MONEY_TYPE           => $this->getEMoneyType(),
            SFields::E_ORDER_DATA           => $this->getEOrderData(),
            SFields::ERROR_CODE             => $this->getErrorCode(),
            SFields::ERROR_COMMENT          => $this->getErrorComment(),
            SFields::FIRST_NAME             => $this->getFirstName(),
            SFields::GDS_PAYMENT_PURPOSE_ID => $this->getGdsPaymentPurposeId(),
            SFields::I_DATA                 => $this->getIData(),
            SFields::IP_ADDRESS             => $this->getIp(),
            SFields::LAST_NAME              => $this->getLastName(),
            SFields::LOAN_ID                => $this->getLoanId(),
            SFields::MESSAGE                => $this->getMessage(),
            SFields::MIDDLE_NAME            => $this->getMiddleName(),
            SFields::NEED_CONFIRM           => $this->getNeedConfirm(),
            SFields::ORDER_NUMBER           => $this->getOrderNumber(),
            SFields::PARENT_ORDER_NUMBER    => $this->getParentOrderNumber(),
            SFields::PAYMENT_TYPE           => $this->getPaymentType(),
            SFields::PHONE                  => $this->getPhone(),
            SFields::PT_CODE                => $this->getPtCode(),
            SFields::RECOMMENDATION         => $this->getRecommendation(),
            SFields::RESPONSE_CODE          => $this->getResponseCode(),
            'Response_Message'              => $this->getResponseMessage(),
            SFields::STATUS                 => $this->getStatus(),
            SFields::TOTAL                  => $this->getTotal(),
            SFields::SUM                    => $this->getSum(),
            SFields::SBER_ORDER_ID          => $this->getSberOrderId(),
            SFields::RECEIPT                => $this->getReceipts(),
            SFields::QRC_ID                 => $this->getQrcId(),
            SFields::IS_OTHER_CARD          => $this->isOtherCard(),
            SFields::ACQUIRER_ID            => $this->getAcquirerID(),
            SFields::PROCESSING_NAME        => $this->getProcessingName(),
            SFields::PROTOCOL_TYPE_NAME     => $this->getProtocolTypeName(),
            SFields::CARD_SUB_TYPE          => $this->getCardSubType(),
            SFields::RATE                   => $this->getRate(),
            SFields::COUNTRY                => $this->getCountry(),
            SFields::TOKEN_IDP              => $this->getTokenIdp(),
            SFields::GIFT_CERT              => $this->getGiftCert(),
            SFields::OPKC_ID                => $this->getOpkcID(),
            SFields::TPAY_REQUEST_ID        => $this->getTpayRequestId(),
            SFields::BNPL_REQUEST_ID        => $this->getBnplRequestId(),
        ], static function ($value) {
            return $value !== null;
        });
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode(
            $this->toArray(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
