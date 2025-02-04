<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * ОФД (Оператор Фискальных Данных).
 * Fiscal Data Operator" (FDO)
 */
class FiscalDataOperator implements \JsonSerializable
{
    /**
     * Название компании ОФД.
     *
     * @var string
     */
    private $name;

    /**
     * ИНН оператора фискальных данных.
     *
     * @var string
     */
    private $inn;

    /**
     * Адрес сайта ОФД.
     *
     * @var string
     */
    private $www;

    /**
     * @param string $name Название компании ОФД.
     * @param string $inn ИНН оператора фискальных данных.
     * @param string $www Адрес сайта ОФД.
     */
    public function __construct(string $name, string $inn, string $www)
    {
        $this->name = $name;
        $this->inn = $inn;
        $this->www = $www;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'inn'  => $this->inn,
            'www'  => $this->www,
        ];
    }
}
