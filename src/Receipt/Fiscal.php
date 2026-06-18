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
     * Идентификатор чека в Uniteller.
     *
     * @var string
     */
    protected $id;

    /**
     * Формат Y-m-d H:i:s.
     *
     * @var string|null
     */
    protected $date;

    /**
     * Тип документа/чека.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\DocumentType
     *
     * @var int
     */
    protected $type;

    /**
     * Информация о регистрации чека.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\Register
     */
    protected $register;

    /**
     * Информация о ККМ (контрольно-кассовой машине).
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister
     */
    protected $ecr;

    /**
     * Информации о компании.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\Company
     */
    protected $company;

    /**
     * Информация об ОФД (Операторе Фискальных Данных).
     *
     * @var \Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator
     */
    protected $fdo;

    /**
     * Опциональный параметр с произвольными данными от мерчанта. Транслируется в неизменном виде во всех
     * фискализированных чеках, созданных в процессе оплаты по исходному чеку для фискализации.
     * Формат: json-объект произвольной внутренней структуры.
     *
     * @var array|null
     */
    protected $optional;

    /**
     * Дополнительные параметры платежа.
     * Этот блок может отсутствовать целиком, или в нем могут отсутствовать какие-то элементы.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Params|null
     */
    protected $params;

    /**
     * @param string $id Уникальный идентификатор чека в платежном шлюзе Uniteller.
     * @param string|null $date Формат Y-m-d H:i:s.
     * @param int $type Тип документа. Смотри класс \Tmconsulting\Uniteller\Receipt\Enum\DocumentType.
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister $ecr Информация о ККМ (контрольно-кассовой машине).
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\Company $company Информации о компании.
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator $fdo Информация об ОФД (Операторе Фискальных Данных).
     * @param \Tmconsulting\Uniteller\Receipt\Fiscal\Register $register Информация о регистрации чека.
     * @param array|null $optional Произвольные данные от мерчанта.
     * @param \Tmconsulting\Uniteller\Receipt\Params|null $params Дополнительные параметры платежа.
     */
    public function __construct(
        string                 $id,
        ?string                $date,
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
            'type'     => $this->type,
            'register' => $this->register,
            'ecr'      => $this->ecr,
            'company'  => $this->company,
            'fdo'      => $this->fdo,
        ];
        if ($this->date !== null) {
            $arr['date'] = $this->date;
        }
        if ($this->optional !== null) {
            $arr['optional'] = $this->optional;
        }
        if ($this->params !== null) {
            $arr['params'] = $this->params;
        }
        return $arr;
    }

    /**
     * Уникальный идентификатор чека в платежном шлюзе Uniteller.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Формат Y-m-d H:i:s.
     *
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * Тип документа.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\DocumentType
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Информация о регистрации чека.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Fiscal\Register
     */
    public function getRegister(): Register
    {
        return $this->register;
    }

    /**
     * Информация о ККМ (контрольно-кассовой машине).
     *
     * @return \Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister
     */
    public function getEcr(): ElectronicCashRegister
    {
        return $this->ecr;
    }

    /**
     * Информация о компании.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Fiscal\Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * Информация об ОФД (Операторе Фискальных Данных).
     *
     * @return \Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator
     */
    public function getFdo(): FiscalDataOperator
    {
        return $this->fdo;
    }

    /**
     * Опциональный параметр с произвольными данными от мерчанта.
     * Транслируется в неизменном виде во всех фискализированных чеках,
     * созданных в процессе оплаты по исходному чеку для фискализации.
     *
     * Формат: JSON-объект произвольной внутренней структуры.
     *
     * @return array|null
     */
    public function getOptional(): ?array
    {
        return $this->optional;
    }

    /**
     * Дополнительные параметры платежа.
     * Блок может отсутствовать целиком или содержать не все элементы.
     *
     * В параметре place можно указать URL одного из сайтов,
     * перечисленных в Личном кабинете налоговой мерчанта.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Params|null
     */
    public function getParams(): ?Params
    {
        return $this->params;
    }
}
