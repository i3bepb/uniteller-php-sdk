<?php

namespace Tmconsulting\Uniteller\Receipt;

/**
 * Данные для чека ФФД 1.2
 */
class Receipt implements \JsonSerializable
{
    /**
     * Контакты плательщика для отправки текста фискального чека.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Customer|null
     */
    protected $customer;

    /**
     * Опциональный блок данных кассира.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Cashier|null
     */
    protected $cashier;

    /**
     * Значение системы налогообложения.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Taxmode
     *
     * @var int
     */
    protected $taxmode;

    /**
     * Массив товарных позиций в чеке. Должен содержать хотя бы один элемент.
     * Общая сумма по всем позициям должна быть равна общей сумме чека.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item[]
     */
    protected $lines = [];

    /**
     * Опциональный параметр с произвольными данными от мерчанта.
     * Транслируется в неизменном виде во всех фискализированных чеках, созданных в процессе оплаты по данному чеку.
     * Формат: json-объект произвольной внутренней структуры.
     *
     * @var array|null
     */
    protected $optional = [];

    /**
     * Адрес электронной почты отправителя чека.
     *
     * @var string|null
     */
    protected $senderEmail;

    /**
     * Дополнительные параметры платежа.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Params|null
     */
    protected $params;

    /**
     * Информация об оплате.
     *
     * @var \Tmconsulting\Uniteller\Receipt\PaymentInfo[]
     */
    protected $payments = [];

    /**
     * Итоговая сумма чека.
     *
     * @var string
     */
    protected $total;

    /**
     * Отраслевой реквизит чека.
     *
     * @var \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null
     */
    protected $industryProps;

    /**
     * Тип коррекции.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\CorrectionType
     *
     * @var int|null
     */
    protected $correctionType;

    /**
     * Номер документа основания коррекции.
     *
     * @var string|null
     */
    protected $causeDocumentNumber;

    /**
     * Дата документа основания коррекции.
     *
     * @var string|null
     */
    protected $causeDocumentDate;

    /**
     * Предмет коррекции.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\CorrectionSubject
     *
     * @var int|null
     */
    protected $correctionSubject;

    /**
     * Признак расчёта в сети Интернет (тег 1125).
     *
     * При продаже в сети Интернет должен передаваться со значением 1.
     * Допустимые значения: 0 — расчёт производится не в сети Интернет, 1 — расчёт производится в сети Интернет.
     *
     * Если параметр не передан, используется значение, заданное при регистрации ККТ.
     * При значении 1 в данных покупателя должен присутствовать номер телефона или адрес электронной
     * почты, а в параметре place — URL интернет-магазина.
     *
     * @var int|null
     */
    protected $internet;

    /**
     * Часовая зона места осуществления расчёта (тег 1011).
     *
     * Параметр обязателен при продаже товара, подлежащего обязательной маркировке.
     * Допустимые значения — целые числа от 1 до 11, соответствующие часовым зонам РФ.
     * Если параметр не передан, используется значение 2 — московское время (UTC+3).
     *
     * @var int|null
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Timezone
     */
    protected $timezone;

    /**
     * Дополнительный реквизит чека (тег 1192).
     * Содержит дополнительную информацию, включаемую в фискальный чек. Максимальная длина значения — 16 символов.
     *
     * @var string|null
     */
    protected $additionalRequisite;

    /**
     * @param int $taxmode Значение системы налогообложения. Смотри \Tmconsulting\Uniteller\Receipt\Enum\Taxmode.
     * @param \Tmconsulting\Uniteller\Receipt\Item[] $lines Массив товарных позиций в чеке.
     * @param \Tmconsulting\Uniteller\Receipt\PaymentInfo[] $payments Информация об оплате.
     * @param float|int|string $total Итоговая сумма чека.
     * @param string|null $senderEmail Адрес электронной почты отправителя чека.
     * @param array|null $optional Произвольные данными от Мерчанта - json-объект произвольной внутренней структуры.
     * @param \Tmconsulting\Uniteller\Receipt\Customer|null $customer Контакты плательщика для отправки текста фискального чека.
     * @param \Tmconsulting\Uniteller\Receipt\Cashier|null $cashier Данные кассира.
     * @param int|null $internet 0 — расчёт производится не в сети Интернет, 1 — расчёт производится в сети Интернет.
     * @param int|null $timezone Целое число от 1 до 11, соответствующее часовой зоне РФ.
     * @param string|null $additionalRequisite Дополнительный реквизит чека (тег 1192).
     * @param \Tmconsulting\Uniteller\Receipt\Params|null $params Дополнительные параметры платежа.
     * @param \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null $industryProps Отраслевой реквизит чека.
     * @param int|null $correctionType Тип коррекции. Смотри \Tmconsulting\Uniteller\Receipt\Enum\CorrectionType.
     * @param string|null $causeDocumentNumber Номер документа основания коррекции.
     * @param string|null $causeDocumentDate Дата документа основания коррекции.
     * @param int|null $correctionSubject Предмет коррекции. Смотри \Tmconsulting\Uniteller\Receipt\Enum\CorrectionSubject.
     */
    public function __construct(
        int       $taxmode,
        array     $lines,
        array     $payments,
                  $total,
        ?string   $senderEmail = null,
        ?array    $optional = null,
        ?Customer $customer = null,
        ?Cashier  $cashier = null,
        ?int      $internet = null,
        ?int      $timezone = null,
        ?string   $additionalRequisite = null,
        ?Params   $params = null,
        ?array    $industryProps = null,
        ?int      $correctionType = null,
        ?string   $causeDocumentNumber = null,
        ?string   $causeDocumentDate = null,
        ?int      $correctionSubject = null
    )
    {
        $this->taxmode = $taxmode;
        $this->customer = $customer;
        $this->cashier = $cashier;
        $this->lines = $lines;
        $this->senderEmail = $senderEmail;
        $this->optional = $optional;
        $this->internet = $internet;
        $this->timezone = $timezone;
        $this->additionalRequisite = $additionalRequisite;
        $this->params = $params;
        $this->payments = $payments;
        $this->total = (string)$total;
        $this->setIndustryProps($industryProps);
        $this->correctionType = $correctionType;
        $this->causeDocumentNumber = $causeDocumentNumber;
        $this->causeDocumentDate = $causeDocumentDate;
        $this->correctionSubject = $correctionSubject;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'taxmode'  => $this->taxmode,
            'lines'    => $this->lines,
            'payments' => $this->payments,
            'total'    => $this->total,
        ];
        if ($this->optional !== null) {
            $arr['optional'] = $this->optional;
        }
        if ($this->customer !== null) {
            $arr['customer'] = $this->customer;
        }
        if ($this->cashier !== null) {
            $arr['cashier'] = $this->cashier;
        }
        if ($this->internet !== null) {
            $arr['internet'] = $this->internet;
        }
        if ($this->timezone !== null) {
            $arr['timezone'] = $this->timezone;
        }
        if ($this->additionalRequisite !== null) {
            $arr['additionalRequisite'] = $this->additionalRequisite;
        }
        if ($this->params !== null) {
            $arr['params'] = $this->params;
        }
        if ($this->senderEmail !== null) {
            $arr['senderemail'] = $this->senderEmail;
        }
        if ($this->industryProps !== null) {
            $arr['industryProps'] = $this->industryProps;
        }
        if ($this->correctionType !== null) {
            $arr['correctionType'] = $this->correctionType;
        }
        if ($this->causeDocumentNumber !== null) {
            $arr['causeDocumentNumber'] = $this->causeDocumentNumber;
        }
        if ($this->causeDocumentDate !== null) {
            $arr['causeDocumentDate'] = $this->causeDocumentDate;
        }
        if ($this->correctionSubject !== null) {
            $arr['correctionSubject'] = $this->correctionSubject;
        }
        return $arr;
    }

    /**
     * @return string Json представление чека.
     */
    public function toJson(): string
    {
        return json_encode($this);
    }

    /**
     * @return string Чек в формате base64 для запроса.
     */
    public function toBase64(): string
    {
        return base64_encode($this->toJson());
    }

    /**
     * @return \Tmconsulting\Uniteller\Receipt\Item[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * Контакты плательщика для отправки текста фискального чека.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Customer|null
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * Данные кассира.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Cashier|null
     */
    public function getCashier(): ?Cashier
    {
        return $this->cashier;
    }

    /**
     * Значение системы налогообложения.
     *
     * @return int
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Taxmode
     */
    public function getTaxmode(): int
    {
        return $this->taxmode;
    }

    /**
     * Опциональный параметр с произвольными данными от мерчанта.
     * Транслируется в неизменном виде во всех фискализированных чеках, созданных в процессе оплаты по данному чеку.
     *
     * Формат: json-объект произвольной внутренней структуры.
     *
     * @return array|null
     */
    public function getOptional(): ?array
    {
        return $this->optional;
    }

    /**
     * Адрес электронной почты отправителя чека.
     *
     * @return string|null
     */
    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    /**
     * Дополнительные параметры платежа.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     * В параметре place можно указать url одного из сайтов, перечисленных в Личном кабинете налоговой мерчанта.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Params|null
     */
    public function getParams(): ?Params
    {
        return $this->params;
    }

    /**
     * Информация об оплате.
     *
     * @return \Tmconsulting\Uniteller\Receipt\PaymentInfo[]
     */
    public function getPayments(): array
    {
        return $this->payments;
    }

    /**
     * Итоговая сумма чека.
     *
     * @return string
     */
    public function getTotal(): string
    {
        return $this->total;
    }

    /**
     * Возвращает отраслевой реквизит чека.
     *
     * @return \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null
     */
    public function getIndustryProps(): ?array
    {
        return $this->industryProps;
    }

    /**
     * @param array|null $industryProps
     */
    public function setIndustryProps(?array $industryProps)
    {
        $this->industryProps = !empty($industryProps) ? $industryProps : null;
    }

    /**
     * Возвращает тип коррекции.
     *
     * @return int|null
     */
    public function getCorrectionType(): ?int
    {
        return $this->correctionType;
    }

    /**
     * Возвращает номер документа основания коррекции.
     *
     * @return string|null
     */
    public function getCauseDocumentNumber(): ?string
    {
        return $this->causeDocumentNumber;
    }

    /**
     * Возвращает дату документа основания коррекции.
     *
     * @return string|null
     */
    public function getCauseDocumentDate(): ?string
    {
        return $this->causeDocumentDate;
    }

    /**
     * Предмет коррекции.
     *
     * @return int|null
     */
    public function getCorrectionSubject(): ?int
    {
        return $this->correctionSubject;
    }

    /**
     * Признак расчёта в сети Интернет (тег 1125).
     *
     * @return int|null 0 — расчёт производится не в сети Интернет, 1 — расчёт производится в сети Интернет.
     */
    public function getInternet(): ?int
    {
        return $this->internet;
    }

    /**
     * Часовая зона места осуществления расчёта (тег 1011).
     *
     * @return int|null Целое число от 1 до 11, соответствующее часовой зоне РФ.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Timezone
     */
    public function getTimezone(): ?int
    {
        return $this->timezone;
    }

    /**
     * Дополнительный реквизит чека (тег 1192).
     *
     * @return string|null
     */
    public function getAdditionalRequisite(): ?string
    {
        return $this->additionalRequisite;
    }
}
