<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * Информации о компании.
 */
class Company implements \JsonSerializable
{
    /**
     * Наименование компании.
     *
     * @var string
     */
    protected $name;

    /**
     * ИНН
     *
     * @var string
     */
    protected $inn;

    /**
     * @param string $name Наименование компании.
     * @param string $inn ИНН.
     */
    public function __construct(string $name, string $inn)
    {
        $this->name = $name;
        $this->inn = $inn;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'inn'  => $this->inn,
        ];
    }

    /**
     * Наименование компании.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * ИНН
     *
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }
}
