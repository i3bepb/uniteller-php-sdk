<?php

namespace Tmconsulting\Uniteller\Receipt\Item;

/**
 * Дробное количество маркированного товара.
 */
class QtyPart implements \JsonSerializable
{
    /**
     * Числитель.
     *
     * @var int
     */
    private $numerator;

    /**
     * Знаменатель.
     *
     * @var int
     */
    private $denominator;

    /**
     * @param int $numerator Числитель.
     * @param int $denominator Знаменатель.
     */
    public function __construct(int $numerator, int $denominator)
    {
        $this->numerator = $numerator;
        $this->denominator = $denominator;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'numerator' => $this->numerator,
            'denominator' => $this->denominator,
        ];
    }
}
