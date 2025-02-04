<?php

namespace Tmconsulting\Uniteller\Receipt\Fiscal;

/**
 * Информации о ККМ (контрольно-кассовая машина).
 * Electronic Cash Register (ECR).
 */
class ElectronicCashRegister implements \JsonSerializable
{
    /**
     * Заводской номер ККМ.
     *
     * @var string
     */
    private $sn;

    /**
     * Регистрационный номер ККМ.
     *
     * @var string
     */
    private $rn;

    /**
     * Номер фискального накопителя.
     *
     * @var string
     */
    private $fs;

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
}
