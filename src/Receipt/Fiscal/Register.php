<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * Информация о регистрации чека.
 */
class Register implements \JsonSerializable
{
    /**
     * Фискальный номер документа.
     *
     * @var string
     */
    protected $fiscalNumber;

    /**
     * Номер смены.
     *
     * @var string
     */
    protected $shiftNumber;

    /**
     * Номер чека внутри смены.
     *
     * @var string
     */
    protected $shiftIndex;

    /**
     * Дата регистрации документа в ФН.
     *
     * @var string
     */
    protected $fiscalDate;

    /**
     * Фискальный признак документа - криптографически сформированное значение, которым фискальный
     * накопитель "подписывает" конкретный фискальный документ/чек.
     *
     * @var string
     */
    protected $fiscalAttr;

    /**
     * Дата регистрации документа в ОФД.
     *
     * @var string
     */
    protected $fdoDate;

    /**
     * Фискальный признак от ОФД.
     *
     * @var string
     */
    protected $fdoAttr;

    /**
     * Ссылка для проверки чека на сайте налоговой.
     *
     * @var string|null
     */
    protected $fiscalLink;

    /**
     * ФФД «QR-код», тег 1196.
     *
     * @var string|null
     */
    protected $qr;

    /**
     * Результаты проверки кодов маркировки товаров.
     *
     * @var array|null
     */
    protected $markingInfo;

    /**
     * @param string $fiscalNumber Фискальный номер документа.
     * @param string $shiftNumber Номер смены.
     * @param string $shiftIndex Номер чека внутри смены.
     * @param string $fiscalDate Дата регистрации документа в ФН.
     * @param string $fiscalAttr Фискальный признак документа.
     * @param string $fdoDate Дата регистрации документа в ОФД.
     * @param string $fdoAttr Фискальный признак от ОФД.
     * @param string|null $fiscalLink Ссылка для проверки чека на сайте налоговой.
     * @param string|null $qr ФФД «QR-код», тег 1196.
     * @param array|null $markingInfo Результаты проверки кодов маркировки товаров.
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
    ) {
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
        if ($this->fiscalLink !== null) {
            $arr['fiscal_link'] = $this->fiscalLink;
        }
        if ($this->qr !== null) {
            $arr['qr'] = $this->qr;
        }
        if ($this->markingInfo !== null) {
            $arr['markinginfo'] = $this->markingInfo;
        }

        return $arr;
    }

    /**
     * Возвращает фискальный номер документа.
     *
     * @return string
     */
    public function getFiscalNumber(): string
    {
        return $this->fiscalNumber;
    }

    /**
     * Возвращает номер смены.
     *
     * @return string
     */
    public function getShiftNumber(): string
    {
        return $this->shiftNumber;
    }

    /**
     * Возвращает номер чека внутри смены.
     *
     * @return string
     */
    public function getShiftIndex(): string
    {
        return $this->shiftIndex;
    }

    /**
     * Возвращает дату регистрации документа в ФН.
     *
     * @return string
     */
    public function getFiscalDate(): string
    {
        return $this->fiscalDate;
    }

    /**
     * Возвращает фискальный признак документа.
     *
     * @return string
     */
    public function getFiscalAttr(): string
    {
        return $this->fiscalAttr;
    }

    /**
     * Возвращает дату регистрации документа в ОФД.
     *
     * @return string
     */
    public function getFdoDate(): string
    {
        return $this->fdoDate;
    }

    /**
     * Возвращает фискальный признак от ОФД.
     *
     * @return string
     */
    public function getFdoAttr(): string
    {
        return $this->fdoAttr;
    }

    /**
     * Возвращает ссылку для проверки чека на сайте налоговой.
     *
     * @return string|null
     */
    public function getFiscalLink(): ?string
    {
        return $this->fiscalLink;
    }

    /**
     * ФФД «QR-код», тег 1196.
     *
     * @return string|null
     */
    public function getQr(): ?string
    {
        return $this->qr;
    }

    /**
     * Результаты проверки кодов маркировки товаров.
     * Структура поля в документации Uniteller не описана.
     *
     * @return array|null
     */
    public function getMarkingInfo(): ?array
    {
        return $this->markingInfo;
    }
}
