<?php

namespace Tmconsulting\Uniteller\Receipt;

use Tmconsulting\Uniteller\Support\RawDataAwareTrait;

/**
 * Отраслевой реквизит чека.
 */
class IndustryProps implements \JsonSerializable
{
    use RawDataAwareTrait;

    /**
     * Идентификатор ФОИВ (Федеральный Орган Исполнительной Власти).
     *
     * @var string|null
     */
    protected $id;

    /**
     * Дата документа основания.
     *
     * @var string|null
     */
    protected $date;

    /**
     * Номер документа основания.
     *
     * @var string|null
     */
    protected $number;

    /**
     * Значение отраслевого реквизита.
     *
     * Формат: param1=value1&param2=value2...
     * Если значение содержит символ &, он передается как &&.
     *
     * @var string|null
     */
    protected $values;

    /**
     * @param string|null $id Идентификатор ФОИВ (Федеральный Орган Исполнительной Власти).
     * @param string|null $date Дата документа основания.
     * @param string|null $number Номер документа основания.
     * @param string|null $values Значение отраслевого реквизита.
     * @param array $rawData Исходные данные, т.е. ассоциативный массив, который получился из json.
     *
     * @throws \ReflectionException
     */
    public function __construct(
        ?string $id = null,
        ?string $date = null,
        ?string $number = null,
        ?string $values = null,
        array   $rawData = []
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->number = $number;
        $this->values = $values;
        $this->setRawData($rawData);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [];
        if ($this->id !== null) {
            $arr['id'] = $this->id;
        }
        if ($this->date !== null) {
            $arr['date'] = $this->date;
        }
        if ($this->number !== null) {
            $arr['number'] = $this->number;
        }
        if ($this->values !== null) {
            $arr['values'] = $this->values;
        }

        return $arr;
    }

    /**
     * Возвращает идентификатор ФОИВ (Федеральный Орган Исполнительной Власти).
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null Дата документа основания.
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @return string|null Номер документа основания.
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * Значение отраслевого реквизита.
     *
     * Формат: param1=value1&param2=value2...
     * Если значение содержит символ &, он передается как &&.
     *
     * @return string|null
     */
    public function getValues(): ?string
    {
        return $this->values;
    }
}
