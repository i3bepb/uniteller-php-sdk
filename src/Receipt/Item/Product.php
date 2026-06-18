<?php

namespace Tmconsulting\Uniteller\Receipt\Item;

/**
 * Дополнительные сведения о продукте.
 */
class Product implements \JsonSerializable
{
    /**
     * Код товара.
     *
     * Может быть передан в двух вариантах:
     * 1. Строка длиной до 256 символов.
     * 2. Строка длиной до 512 символов в HEX-формате, начинающаяся с 0x, например: 0x444D02A3357919913962546563775844562e705a46
     *
     * Второй вариант используется, когда невозможно поместить в JSON строку, содержащую символы, не соответствующие UTF-8.
     *
     * Если на упаковке присутствует как код маркировки, так и штрих-код, то необходимо передать штрих-код. Если в коде
     * маркировки встречается символ FNC1, то он должен передаваться как байт с кодом 0x1d.
     *
     * @var string
     */
    protected $kt;

    /**
     * Акциз.
     *
     * @var string
     */
    protected $exc;

    /**
     * Код страны происхождения товара.
     *
     * @var string
     */
    protected $coc;

    /**
     * Номер таможенной декларации.
     *
     * @var string
     */
    protected $ncd;

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

    /**
     * Возвращает код товара.
     *
     * @return string
     */
    public function getKt(): string
    {
        return $this->kt;
    }

    /**
     * Возвращает акциз.
     *
     * @return string
     */
    public function getExc(): string
    {
        return $this->exc;
    }

    /**
     * Возвращает код страны происхождения товара.
     *
     * @return string
     */
    public function getCoc(): string
    {
        return $this->coc;
    }

    /**
     * Возвращает номер таможенной декларации.
     *
     * @return string
     */
    public function getNcd(): string
    {
        return $this->ncd;
    }
}
