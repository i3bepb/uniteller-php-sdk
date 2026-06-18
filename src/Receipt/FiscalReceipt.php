<?php

namespace Tmconsulting\Uniteller\Receipt;

use Tmconsulting\Uniteller\Support\RawDataAwareTrait;

class FiscalReceipt extends Receipt
{
    use RawDataAwareTrait;

    /**
     * Данные, полученные в процессе фискализации.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal
     */
    protected $fiscal;

    /**
     * @var array|null
     */
    protected $userRequisite;

    /**
     * @param array $rawData Исходные данные, т.е. ассоциативный массив, который получился из json.
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal $fiscal Данные, полученные в процессе фискализации.
     * @param int $taxmode Значение системы налогообложения.
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
     * @param array|null $userRequisite
     * @param \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null $industryProps Отраслевой реквизит чека.
     * @param int|null $correctionType Тип коррекции. Смотри \Tmconsulting\Uniteller\Receipt\Enum\CorrectionType.
     * @param string|null $causeDocumentNumber Номер документа основания коррекции.
     * @param string|null $causeDocumentDate Дата документа основания коррекции.
     * @param int|null $correctionSubject Предмет коррекции. Смотри \Tmconsulting\Uniteller\Receipt\Enum\CorrectionSubject.
     *
     * @throws \ReflectionException
     */
    public function __construct(
        array     $rawData,
        Fiscal    $fiscal,
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
        ?array    $userRequisite = null,
        array     $industryProps = null,
        ?int      $correctionType = null,
        ?string   $causeDocumentNumber = null,
        ?string   $causeDocumentDate = null,
        ?int      $correctionSubject = null
    ) {
        parent::__construct(
            $taxmode,
            $lines,
            $payments,
            $total,
            $senderEmail,
            $optional,
            $customer,
            $cashier,
            $internet,
            $timezone,
            $additionalRequisite,
            $params,
            $industryProps,
            $correctionType,
            $causeDocumentNumber,
            $causeDocumentDate,
            $correctionSubject
        );
        $this->fiscal = $fiscal;
        $this->userRequisite = $userRequisite;
        $this->setRawData($rawData);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = parent::jsonSerialize();
        $arr['fiscal'] = $this->fiscal;
        if ($this->userRequisite !== null) {
            $arr['userrequisite'] = $this->userRequisite;
        }
        return $arr;
    }

    /**
     * Данные, полученные в процессе фискализации.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Fiscal
     */
    public function getFiscal(): Fiscal
    {
        return $this->fiscal;
    }

    /**
     * @return array|null
     */
    public function getUserRequisite(): ?array
    {
        return $this->userRequisite;
    }
}
