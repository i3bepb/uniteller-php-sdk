<?php

namespace Tmconsulting\Uniteller\Receipt;

class Cashier implements \JsonSerializable
{
    /**
     * ФИО кассира.
     * Максимально 240 символов включительно.
     *
     * @var string|null
     */
    private $name;

    /**
     * ИНН кассира.
     * 10 цифр.
     *
     * @var int
     */
    private $inn;

    /**
     * @param string|null $name ФИО кассира. Максимально 240 символов включительно.
     * @param int|string|null $inn ИНН кассира. 10 цифр.
     */
    public function __construct(?string $name = null, $inn = null)
    {
        $this->name = $name;
        $this->inn = (int)$inn;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'inn'  => $this->inn,
        ];
    }
}
