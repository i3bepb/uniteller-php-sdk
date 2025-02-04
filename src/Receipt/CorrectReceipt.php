<?php

namespace Tmconsulting\Uniteller\Receipt;

class CorrectReceipt implements \JsonSerializable
{
    /**
     * Тип коррекции.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\CorrectionType
     *
     * @var int
     */
    private $correctionType;

    /**
     * Дата документа основания для коррекции.
     * Формат YYYY-MM-DD.
     *
     * @var \DateTime
     */
    private $causeDocumentDate;

    /**
     * Номер документа основания для коррекции.
     *
     * @var string
     */
    private $causeDocumentNumber;

    /**
     * Значение системы налогообложения.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Taxmode
     *
     * @var int
     */
    private $taxmode;

    /**
     * Обязательный блок информации об оплате дополнительными платежными средствами.
     *
     * @var \Tmconsulting\Uniteller\Receipt\PaymentInfo[]
     */
    private $payments = [];

    /**
     * Итоговая сумма чека.
     *
     * @var float
     */
    private $total;

    /**
     * Сумма НДС чека по ставке 20%.
     * Обязательное поле, содержащие неотрицательное значения.
     *
     * @var float
     */
    private $tax1Sum;

    /**
     * Сумма НДС чека по ставке 10%.
     * Обязательное поле, содержащие неотрицательное значения.
     *
     * @var float
     */
    private $tax2Sum;

    /**
     * Сумма расчета по чеку с НДС по ставке 0%.
     * Обязательное поле, содержащие неотрицательное значения.
     *
     * @var float
     */
    private $tax3Sum;

    /**
     * Сумма расчета по чеку без НДС.
     * Обязательное поле, содержащие неотрицательное значения.
     *
     * @var float
     */
    private $tax4Sum;

    /**
     * Сумма НДС чека по расч. ставке 20/120.
     * Обязательное поле, содержащие неотрицательное значения.
     *
     * @var float
     */
    private $tax5Sum;

    /**
     * Сумма НДС чека по расч. ставке 10/110.
     * Обязательное поле, содержащие неотрицательное значения.
     *
     * @var float
     */
    private $tax6Sum;

    /**
     * @param int $correctionType Тип коррекции. Смотри класс \Tmconsulting\Uniteller\Receipt\Enum\CorrectionType.
     * @param \DateTime $causeDocumentDate Дата документа основания для коррекции.
     * @param string $causeDocumentNumber Номер документа основания для коррекции.
     * @param int $taxmode Значение системы налогообложения. Смотри класс \Tmconsulting\Uniteller\Receipt\Enum\Taxmode.
     * @param \Tmconsulting\Uniteller\Receipt\PaymentInfo[] $payments Информация об оплате дополнительными платежными средствами.
     * @param float $total Итоговая сумма чека.
     * @param float $tax1Sum Сумма НДС чека по ставке 20%.
     * @param float $tax2Sum Сумма НДС чека по ставке 10%.
     * @param float $tax3Sum Сумма расчета по чеку с НДС по ставке 0%.
     * @param float $tax4Sum Сумма расчета по чеку без НДС.
     * @param float $tax5Sum Сумма НДС чека по расч. ставке 20/120.
     * @param float $tax6Sum Сумма НДС чека по расч. ставке 10/110.
     */
    public function __construct(
        int       $correctionType,
        \DateTime $causeDocumentDate,
        string    $causeDocumentNumber,
        int       $taxmode,
        array     $payments,
        float     $total,
        float     $tax1Sum,
        float     $tax2Sum,
        float     $tax3Sum,
        float     $tax4Sum,
        float     $tax5Sum,
        float     $tax6Sum
    )
    {
        $this->correctionType = $correctionType;
        $this->causeDocumentDate = $causeDocumentDate;
        $this->causeDocumentNumber = $causeDocumentNumber;
        $this->taxmode = $taxmode;
        $this->payments = $payments;
        $this->total = $total;
        $this->tax1Sum = $tax1Sum;
        $this->tax2Sum = $tax2Sum;
        $this->tax3Sum = $tax3Sum;
        $this->tax4Sum = $tax4Sum;
        $this->tax5Sum = $tax5Sum;
        $this->tax6Sum = $tax6Sum;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'correctionType'      => $this->correctionType,
            'causeDocumentDate'   => $this->causeDocumentDate->format('Y-m-d'),
            'causeDocumentNumber' => $this->causeDocumentNumber,
            'taxmode'             => $this->taxmode,
            'payments'            => $this->payments,
            'total'               => $this->total,
            'tax1Sum'             => $this->tax1Sum,
            'tax2Sum'             => $this->tax2Sum,
            'tax3Sum'             => $this->tax3Sum,
            'tax4Sum'             => $this->tax4Sum,
            'tax5Sum'             => $this->tax5Sum,
            'tax6Sum'             => $this->tax6Sum,
        ];
    }
}
