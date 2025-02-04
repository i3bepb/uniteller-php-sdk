<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * Информация о ККМ (контрольно-кассовой машине).
 */
class ElectronicCashRegister implements \JsonSerializable
{
    /**
     * Заводской номер ККМ.
     *
     * @var string
     */
    protected $sn;

    /**
     * Регистрационный номер ККМ.
     *
     * @var string
     */
    protected $rn;

    /**
     * Номер фискального накопителя.
     *
     * @var string
     */
    protected $fs;

    /**
     * @param string $sn Заводской номер ККМ.
     * @param string $rn Регистрационный номер ККМ.
     * @param string $fs Номер фискального накопителя.
     */
    public function __construct(string $sn, string $rn, string $fs)
    {
        $this->sn = $sn;
        $this->rn = $rn;
        $this->fs = $fs;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'sn' => $this->sn,
            'rn' => $this->rn,
            'fs' => $this->fs,
        ];
    }

    /**
     * Возвращает заводской номер ККМ.
     *
     * @return string
     */
    public function getSn(): string
    {
        return $this->sn;
    }

    /**
     * Возвращает регистрационный номер ККМ.
     *
     * @return string
     */
    public function getRn(): string
    {
        return $this->rn;
    }

    /**
     * Возвращает номер фискального накопителя.
     *
     * @return string
     */
    public function getFs(): string
    {
        return $this->fs;
    }
}
