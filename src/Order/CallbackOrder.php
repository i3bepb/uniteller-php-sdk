<?php

namespace Tmconsulting\Uniteller\Order;

class CallbackOrder implements \JsonSerializable
{
    /**
     * Номер заказа в системе расчётов интернет-магазина.
     *
     * @var string
     */
    protected $orderId;

    /**
     * Статус заказа.
     *
     * @var string
     *
     * @see \Tmconsulting\Uniteller\Order\Status
     */
    protected $status;

    /**
     * @var string
     */
    protected $signature;

    /**
     * Идентификатор банка-эквайера в системе Uniteller.
     *
     * @var string|null
     */
    protected $acquirerId;

    /**
     * Код подтверждения транзакции от процессингового центра.
     *
     * @var string|null
     */
    protected $approvalCode;

    /**
     * Номер платежа в системе Uniteller (RRN).
     *
     * @var string|null
     */
    protected $billNumber;

    /**
     * Идентификатор зарегистрированной карты (передаётся только для уже зарегистрированных в Uniteller карт).
     *
     * @var string|null
     */
    protected $cardId;

    /**
     * Маскированный номер карты (передается только для операций с картой).
     *
     * @var string|null
     */
    protected $cardNumber;

    /**
     * Идентификатор Покупателя.
     *
     * @var string|null
     */
    protected $customerId;

    /**
     * Информация о 3DS (передается только для операций с картой), возможные значения:
     *  5 — 3DS Full;
     *  6 — 3DS-Acquirer only;
     *  7 — E-commerce без 3DS.
     *
     * @var string|null
     */
    protected $eci;

    /**
     * Тип электронной валюты (передается только для операций с электронной валютой).
     *
     * @var string|null
     */
    protected $eMoneyType;

    /**
     * Тип оплаты, возможные значения смотри в классе \Tmconsulting\Uniteller\Parameter\Enum\PaymentType.
     *
     * @var int|null
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\PaymentType
     */
    protected $paymentType;

    /**
     * Сумма всех средств, уплаченных по одному заказу.
     *
     * @var string|null
     */
    protected $total;

    /**
     * Текущий баланс платежа (с учётом частичных отмен и возвратов).
     *
     * @var string|null
     */
    protected $balance;

    /**
     * @param string $orderId
     * @param string $status
     * @param string|null $acquirerId
     * @param string|null $approvalCode
     * @param string|null $billNumber
     * @param string|null $cardId
     * @param string|null $cardNumber
     * @param string|null $customerId
     * @param string|null $eci
     * @param string|null $eMoneyType
     * @param int|null $paymentType
     * @param string|null $total
     * @param string|null $balance
     */
    public function __construct(
        string  $orderId,
        string  $status,
        ?string $acquirerId = null,
        ?string $approvalCode = null,
        ?string $billNumber = null,
        ?string $cardId = null,
        ?string $cardNumber = null,
        ?string $customerId = null,
        ?string $eci = null,
        ?string $eMoneyType = null,
        ?int    $paymentType = null,
        ?string $total = null,
        ?string $balance = null
    )
    {
        $this->orderId = $orderId;
        $this->status = Status::resolve($status);
        $this->acquirerId = $acquirerId;
        $this->approvalCode = $approvalCode;
        $this->billNumber = $billNumber;
        $this->cardId = $cardId;
        $this->cardNumber = $cardNumber;
        $this->customerId = $customerId;
        $this->eci = $eci;
        $this->eMoneyType = $eMoneyType;
        $this->paymentType = $paymentType;
        $this->total = $total;
        $this->balance = $balance;
    }

    /**
     * @return string Номер заказа в системе расчётов интернет-магазина.
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string Статус заказа. Смотри класс \Tmconsulting\Uniteller\Order\Status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @return string|null Идентификатор банка-эквайера в системе Uniteller.
     */
    public function getAcquirerId(): ?string
    {
        return $this->acquirerId;
    }

    /**
     * @return string|null Код подтверждения транзакции от процессингового центра.
     */
    public function getApprovalCode(): ?string
    {
        return $this->approvalCode;
    }

    /**
     * @return string|null Номер платежа в системе Uniteller (RRN).
     */
    public function getBillNumber(): ?string
    {
        return $this->billNumber;
    }

    /**
     * @return string|null Идентификатор зарегистрированной карты (передаётся только для уже зарегистрированных в Uniteller карт).
     */
    public function getCardId(): ?string
    {
        return $this->cardId;
    }

    /**
     * @return string|null Маскированный номер карты (передается только для операций с картой).
     */
    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    /**
     * @return string|null Идентификатор Покупателя.
     */
    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    /**
     * @return string|null Информация о 3DS (передается только для операций с картой).
     */
    public function getEci(): ?string
    {
        return $this->eci;
    }

    /**
     * @return string|null Тип электронной валюты (передается только для операций с электронной валютой).
     */
    public function getEMoneyType(): ?string
    {
        return $this->eMoneyType;
    }

    /**
     * @return int|null Тип оплаты, смотри класс \Tmconsulting\Uniteller\Parameter\Enum\PaymentType.
     */
    public function getPaymentType(): ?int
    {
        return $this->paymentType;
    }

    /**
     * @return string|null Сумма всех средств, уплаченных по одному заказу.
     */
    public function getTotal(): ?string
    {
        return $this->total;
    }

    /**
     * @return string|null Текущий баланс платежа (с учётом частичных отмен и возвратов).
     */
    public function getBalance(): ?string
    {
        return $this->balance;
    }

    /**
     * @param string $signature
     *
     * @return $this
     */
    public function setSignature(string $signature): CallbackOrder
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arr = [
            'Order_ID'     => $this->getOrderId(),
            'Status'       => $this->getStatus(),
        ];
        if ($this->getAcquirerId() !== null) {
            $arr['AcquirerID'] = $this->getAcquirerId();
        }
        if ($this->getApprovalCode() !== null) {
            $arr['ApprovalCode'] = $this->getApprovalCode();
        }
        if ($this->getBalance() !== null) {
            $arr['Balance'] = $this->getBalance();
        }
        if ($this->getBillNumber() !== null) {
            $arr['BillNumber'] = $this->getBillNumber();
        }
        if ($this->getCardNumber() !== null) {
            $arr['CardNumber'] = $this->getCardNumber();
        }
        if ($this->getCardId() !== null) {
            $arr['Card_IDP'] = $this->getCardId();
        }
        if ($this->getCustomerId() !== null) {
            $arr['Customer_IDP'] = $this->getCustomerId();
        }
        if ($this->getEci() !== null) {
            $arr['ECI'] = $this->getEci();
        }
        if ($this->getEMoneyType() !== null) {
            $arr['EMoneyType'] = $this->getEMoneyType();
        }
        if ($this->getPaymentType() !== null) {
            $arr['PaymentType'] = $this->getPaymentType();
        }
        if ($this->getTotal() !== null) {
            $arr['Total'] = $this->getTotal();
        }
        return $arr;
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
