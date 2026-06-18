<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * Информация об ОФД (Операторе Фискальных Данных).
 */
class FiscalDataOperator implements \JsonSerializable
{
    /**
     * Название компании ОФД.
     *
     * @var string
     */
    protected $name;

    /**
     * ИНН оператора фискальных данных.
     *
     * @var string
     */
    protected $inn;

    /**
     * Адрес сайта ОФД.
     *
     * @var string
     */
    protected $www;

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

    /**
     * Возвращает название компании ОФД.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает ИНН оператора фискальных данных.
     *
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * Возвращает адрес сайта ОФД.
     *
     * @return string
     */
    public function getWww(): string
    {
        return $this->www;
    }
}
