<?php

namespace Tmconsulting\Uniteller\Receipt;

class FiscalReceipt extends Receipt implements \JsonSerializable
{
    /**
     * Данные, полученные в процессе фискализации.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal
     */
    private $fiscal;

    /**
     * @var array|null
     */
    private $userRequisite;

    /**
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal $fiscal Данные, полученные в процессе фискализации.
     * @param int $taxmode Значение системы налогообложения.
     * @param \Tmconsulting\Uniteller\Receipt\Item[] $lines Массив товарных позиций в чеке.
     * @param \Tmconsulting\Uniteller\Receipt\PaymentInfo[] $payments Информация об оплате.
     * @param float|int|string $total Итоговая сумма чека.
     * @param string|null $senderemail Адрес электронной почты отправителя чека.
     * @param array|null $optional Произвольные данными от Мерчанта - json-объект произвольной внутренней структуры.
     * @param \Tmconsulting\Uniteller\Receipt\Customer|null $customer Контакты плательщика для отправки текста фискального чека.
     * @param \Tmconsulting\Uniteller\Receipt\Cashier|null $cashier Данные кассира.
     * @param \Tmconsulting\Uniteller\Receipt\Params|null $params Дополнительные параметры платежа.
     * @param array|null $userRequisite
     */
    public function __construct(
        Fiscal    $fiscal,
        int       $taxmode,
        array     $lines,
        array     $payments,
                  $total,
        ?string   $senderemail = null,
        ?array    $optional = null,
        ?Customer $customer = null,
        ?Cashier  $cashier = null,
        ?Params   $params = null,
        ?array    $userRequisite = null
    )
    {
        parent::__construct($taxmode, $lines, $payments, $total, $senderemail, $optional, $customer, $cashier, $params);
        $this->fiscal = $fiscal;
        $this->userRequisite = $userRequisite;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = parent::jsonSerialize();
        $arr['fiscal'] = $this->fiscal;
        if (!empty($this->userRequisite)) {
            $arr['userrequisite'] = json_encode($this->userRequisite);
        }
        return $arr;
    }
}
