<?php

namespace Tmconsulting\Uniteller\Receipt\Item;

/**
 * Дополнительные сведения о продукте.
 */
class Product implements \JsonSerializable
{
    /**
     * Код товара.
     * Может быть передан в 2 вариантах:
     * 1. строка до 32 символов;
     * 2. строка до 66 символов в HEX-формате, начинающаяся с 0x (признак 16-ричного формата), например:
     * "0x444D02A3357919913962546563775844562e705a46"
     * (этот вариант нужен, когда нельзя запаковать в json строку, содержащую символы, несоответствующие UTF-8).
     *
     * @var string
     */
    private $kt;

    /**
     * Акциз.
     *
     * @var string
     */
    private $exc;

    /**
     * Код страны происхождения товара.
     *
     * @var string
     */
    private $coc;

    /**
     * Номер таможенной декларации.
     *
     * @var string
     */
    private $ncd;

    /**
     * @param string $kt Код товара.
     * @param string $exc Акциз.
     * @param string $coc Код страны происхождения товара.
     * @param string $ncd Номер таможенной декларации.
     */
    public function __construct(string $kt, string $exc, string $coc, string $ncd)
    {
        $this->kt = $kt;
        $this->exc = $exc;
        $this->coc = $coc;
        $this->ncd = $ncd;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'kt'  => $this->kt,
            'exc' => $this->exc,
            'coc' => $this->coc,
            'ncd' => $this->ncd,
        ];
    }
}
