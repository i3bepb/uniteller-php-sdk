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
    protected $senderemail;

    /**
     * Дополнительные параметры платежа.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     * В параметре place можно указать url одного из сайтов, перечисленных в Личном кабинете налоговой мерчанта.
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
     * @var float
     */
    protected $total;

    /**
     * @param int $taxmode Значение системы налогообложения. Смотри класс \Tmconsulting\Uniteller\Receipt\Enum\Taxmode.
     * @param \Tmconsulting\Uniteller\Receipt\Item[] $lines Массив товарных позиций в чеке.
     * @param \Tmconsulting\Uniteller\Receipt\PaymentInfo[] $payments Информация об оплате.
     * @param float|int|string $total Итоговая сумма чека.
     * @param string|null $senderemail Адрес электронной почты отправителя чека.
     * @param array|null $optional Произвольные данными от Мерчанта - json-объект произвольной внутренней структуры.
     * @param \Tmconsulting\Uniteller\Receipt\Customer|null $customer Контакты плательщика для отправки текста фискального чека.
     * @param \Tmconsulting\Uniteller\Receipt\Cashier|null $cashier Данные кассира.
     * @param \Tmconsulting\Uniteller\Receipt\Params|null $params Дополнительные параметры платежа.
     */
    public function __construct(
        int       $taxmode,
        array     $lines,
        array     $payments,
                  $total,
        ?string   $senderemail = null,
        ?array    $optional = null,
        ?Customer $customer = null,
        ?Cashier  $cashier = null,
        ?Params $params = null
    )
    {
        $this->customer = $customer;
        $this->cashier = $cashier;
        $this->taxmode = $taxmode;
        $this->lines = $lines;
        $this->senderemail = $senderemail;
        $this->optional = $optional;
        $this->params = $params;
        $this->payments = $payments;
        $this->total = (float)$total;
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
        if (!empty($this->optional)) {
            $arr['optional'] = $this->optional;
        }
        if (!empty($this->customer)) {
            $arr['customer'] = $this->customer;
        }
        if (!empty($this->cashier)) {
            $arr['cashier'] = $this->cashier;
        }
        if (!empty($this->params)) {
            $arr['params'] = $this->params;
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
     * @return string Чек в формате base64 для запроса
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
}
