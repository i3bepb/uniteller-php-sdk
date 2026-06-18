<?php

namespace Tmconsulting\Uniteller\Receipt;

/**
 * Дополнительные параметры платежа.
 */
class Params implements \JsonSerializable
{
    /**
     * Место осуществления расчета.
     * Может содержать URL одного из сайтов, перечисленных в Личном кабинете налоговой мерчанта.
     *
     * @var string
     */
    protected $place;

    /**
     * @param string $place Место расчета.
     */
    public function __construct(string $place)
    {
        $this->place = $place;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'place' => $this->place,
        ];
    }

    /**
     * Возвращает место осуществления расчета.
     * Значение может содержать URL одного из сайтов, перечисленных в Личном кабинете налоговой мерчанта.
     *
     * @return string
     */
    public function getPlace(): string
    {
        return $this->place;
    }
}
