<?php

namespace Tmconsulting\Uniteller\Receipt;

/**
 * Дополнительные параметры платежа.
 */
class Params implements \JsonSerializable
{
    /**
     * Место расчета.
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
}
