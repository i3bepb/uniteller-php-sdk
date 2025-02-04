<?php

namespace Tmconsulting\Uniteller\Receipt;

use Tmconsulting\Uniteller\Receipt\Fiscal\Company;
use Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister;
use Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator;
use Tmconsulting\Uniteller\Receipt\Fiscal\Register;

/**
 * Данные, полученные в процессе фискализации.
 */
class Fiscal implements \JsonSerializable
{
    /**
     * Уникальный идентификатор чека в платежном шлюзе Uniteller.
     *
     * @var string
     */
    private $id;

    /**
     * Дата чека. Формат Y-m-d H:i:s.
     *
     * @var string
     */
    private $date;

    /**
     * Тип документа.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\DocumentType
     *
     * @var int
     */
    private $type;
    /**
     * Информации о регистрации чека.
     * Может отсутствовать или быть пустым.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\Register
     */
    private $register;

    /**
     * Информации о ККМ (контрольно-кассовая машина).
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister
     */
    private $ecr;

    /**
     * Информации о компании.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\Company
     */
    private $company;

    /**
     * Информации об ОФД (Оператор Фискальных Данных).
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator
     */
    private $fdo;

    /**
     * Опциональный параметр с произвольными данными от мерчанта.
     * Транслируется в неизменном виде во всех фискализированных чеках, созданных в процессе оплаты по исходному
     * чеку для фискализации.
     * Формат: json-объект произвольной внутренней структуры.
     *
     * @var array|null
     */
    private $optional = [];

    /**
     * Дополнительные параметры платежа.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     * В параметре place можно указать url одного из сайтов, перечисленных в Личном кабинете налоговой мерчанта.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Params|null
     */
    private $params;

    /**
     * @param string $id Уникальный идентификатор чека в платежном шлюзе Uniteller.
     * @param string $date Дата чека.
     * @param int $type Тип документа. Смотри класс \Tmconsulting\Uniteller\Receipt\Enum\DocumentType.
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister $ecr Информации о ККМ (контрольно-кассовая машина).
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\Company $company Информации о компании.
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator $fdo Информации об ОФД (Оператор Фискальных Данных).
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\Register|null $register Информации о регистрации чека.
     * @param array|null $optional Произвольные данные от мерчанта.
     * @param \Tmconsulting\Uniteller\Receipt\Params|null $params Дополнительные параметры платежа.
     */
    public function __construct(
        string                 $id,
        string                 $date,
        int                    $type,
        ElectronicCashRegister $ecr,
        Company                $company,
        FiscalDataOperator     $fdo,
        Register               $register,
        ?array                 $optional = null,
        ?Params                $params = null
    )
    {
        $this->id = $id;
        $this->date = $date;
        $this->type = $type;
        $this->register = $register;
        $this->ecr = $ecr;
        $this->company = $company;
        $this->fdo = $fdo;
        $this->optional = $optional;
        $this->params = $params;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'id'       => $this->id,
            'date'     => $this->date,
            'type'     => $this->type,
            'register' => $this->register,
            'ecr'      => $this->ecr,
            'company'  => $this->company,
            'fdo'      => $this->fdo,
        ];
        if (!empty($this->optional)) {
            $arr['optional'] = $this->optional;
        }
        if (!empty($this->params)) {
            $arr['params'] = $this->params;
        }
        return $arr;
    }
}
