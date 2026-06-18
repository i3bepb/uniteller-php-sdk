<?php

namespace Tmconsulting\Uniteller\Receipt;

/**
 * Данные кассира.
 */
class Cashier implements \JsonSerializable
{
    /**
     * ФИО кассира. Максимальная длина — 240 символов включительно.
     *
     * @var string|null
     */
    protected $name;

    /**
     * ИНН кассира. Содержит 12 или 10 цифр.
     *
     * @var string|null
     */
    protected $inn;

    /**
     * @param string|null $name ФИО кассира. Максимальная длина — 240 символов включительно.
     * @param int|string|null $inn ИНН кассира. Содержит 12 или 10 цифр.
     */
    public function __construct(?string $name = null, $inn = null)
    {
        $this->name = $name;
        $this->setInn($inn);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [];
        if ($this->name !== null) {
            $arr['name'] = $this->name;
        }
        if ($this->inn !== null) {
            $arr['inn'] = $this->inn;
        }

        return $arr;
    }

    /**
     * Возвращает ФИО кассира. Максимальная длина — 240 символов включительно.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Возвращает ИНН кассира. Содержит 10 цифр.
     *
     * @return string|null
     */
    public function getInn(): ?string
    {
        return $this->inn;
    }

    /**
     * @param int|string|null $inn
     */
    protected function setInn($inn)
    {
        $this->inn = ($inn !== null && $inn !== '' ? (string) $inn : null);
    }
}
