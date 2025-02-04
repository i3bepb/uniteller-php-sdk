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
    private $name;

    /**
     * ИНН
     *
     * @var int
     */
    private $inn;

    /**
     * @param string $name Наименование компании.
     * @param int $inn ИНН.
     */
    public function __construct(string $name, int $inn)
    {
        $this->name = $name;
        $this->inn = $inn;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'inn' => $this->inn,
        ];
    }
}
