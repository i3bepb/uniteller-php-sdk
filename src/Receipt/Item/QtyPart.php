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
    protected $numerator;

    /**
     * Знаменатель.
     *
     * @var int
     */
    protected $denominator;

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
            'numerator'   => $this->numerator,
            'denominator' => $this->denominator,
        ];
    }

    /**
     * Возвращает числитель дробного количества товара.
     *
     * @return int
     */
    public function getNumerator(): int
    {
        return $this->numerator;
    }

    /**
     * Возвращает знаменатель дробного количества товара.
     *
     * @return int
     */
    public function getDenominator(): int
    {
        return $this->denominator;
    }
}
