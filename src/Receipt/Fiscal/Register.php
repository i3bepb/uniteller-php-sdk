<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * Информации о регистрации чека.
 */
class Register implements \JsonSerializable
{
    /**
     * Фискальный номер документа.
     *
     * @var string
     */
    private $fiscalNumber;

    /**
     * Номер смены.
     *
     * @var string
     */
    private $shiftNumber;

    /**
     * Номер чека внутри смены.
     *
     * @var string
     */
    private $shiftIndex;

    /**
     * Дата регистрации документа в ФН.
     *
     * @var string
     */
    private $fiscalDate;

    /**
     * Фискальный признак документа.
     *
     * @var string
     */
    private $fiscalAttr;

    /**
     * Дата регистрации документа в ОФД.
     *
     * @var string
     */
    private $fdoDate;

    /**
     * Фискальный признак от ОФД.
     *
     * @var string
     */
    private $fdoAttr;

    /**
     * Ссылка для проверки чека на сайте налоговой.
     *
     * @var string|null
     */
    private $fiscalLink;

    /**
     * @var string
     */
    private $qr;

    /**
     * @var array
     */
    private $markingInfo;

    /**
     * @param string $fiscalNumber Фискальный номер документа.
     * @param string $shiftNumber Номер смены.
     * @param string $shiftIndex Номер чека внутри смены.
     * @param string $fiscalDate Дата регистрации документа в ФН.
     * @param string $fiscalAttr Фискальный признак документа.
     * @param string $fdoDate Дата регистрации документа в ОФД.
     * @param string $fdoAttr Фискальный признак от ОФД.
     * @param string|null $fiscalLink Ссылка для проверки чека на сайте налоговой.
     * @param string|null $qr
     * @param array|null $markingInfo
     */
    public function __construct(
        string $fiscalNumber,
        string $shiftNumber,
        string $shiftIndex,
        string $fiscalDate,
        string $fiscalAttr,
        string $fdoDate,
        string $fdoAttr,
        ?string $fiscalLink = null,
        ?string $qr = null,
        ?array $markingInfo = null
    )
    {
        $this->fiscalNumber = $fiscalNumber;
        $this->shiftNumber = $shiftNumber;
        $this->shiftIndex = $shiftIndex;
        $this->fiscalDate = $fiscalDate;
        $this->fiscalAttr = $fiscalAttr;
        $this->fdoDate = $fdoDate;
        $this->fdoAttr = $fdoAttr;
        $this->fiscalLink = $fiscalLink;
        $this->qr = $qr;
        $this->markingInfo = $markingInfo;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'fiscal_number' => $this->fiscalNumber,
            'shift_number'  => $this->shiftNumber,
            'shift_index'   => $this->shiftIndex,
            'fiscal_date'   => $this->fiscalDate,
            'fiscal_attr'   => $this->fiscalAttr,
            'fdo_date'      => $this->fdoDate,
            'fdo_attr'      => $this->fdoAttr,
        ];
        if (!empty($this->fiscalLink)) {
            $arr['fiscal_link'] = $this->fiscalLink;
        }
        if (!empty($this->qr)) {
            $arr['qr'] = $this->qr;
        }
        if (!empty($this->markingInfo)) {
            $arr['markinginfo'] = json_encode($this->markingInfo);
        }
        return $arr;
    }
}
