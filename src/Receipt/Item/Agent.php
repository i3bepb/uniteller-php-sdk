<?php

namespace Tmconsulting\Uniteller\Receipt\Item;

class Agent implements \JsonSerializable
{
    /**
     * Признак агента.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\AgentAttribute
     *
     * @var int
     */
    private $agentattr;

    /**
     * Телефон платежного агента.
     *
     * @var string|null
     */
    private $agentphone;

    /**
     * Телефон оператора по приему платежей.
     *
     * @var string|null
     */
    private $accopphone;

    /**
     * Телефон оператора перевода.
     *
     * @var string|null
     */
    private $opphone;

    /**
     * Наименование оператора перевода.
     *
     * @var string|null
     */
    private $opname;

    /**
     * ИНН оператора перевода.
     *
     * @var string|null
     */
    private $opinn;

    /**
     * Адрес оператора перевода.
     *
     * @var string|null
     */
    private $opaddress;

    /**
     * Операция платежного агента.
     *
     * @var string|null
     */
    private $operation;

    /**
     * Наименование поставщика.
     *
     * @var string|null
     */
    private $suppliername;

    /**
     * ИНН поставщика.
     *
     * @var string|null
     */
    private $supplierinn;

    /**
     * Телефон поставщика, строго в формате "+7XXXXXXXXXX" или "+7-XXX-XXX-XX-XX".
     *
     * @var string|null
     */
    private $supplierphone;

    /**
     * @param string $agentattr Признак агента. See \Tmconsulting\Uniteller\Receipt\Enum\AgentAttribute.
     * @param string|null $agentphone Телефон платежного агента.
     * @param string|null $accopphone Телефон оператора по приему платежей.
     * @param string|null $opphone Телефон оператора перевода.
     * @param string|null $opname Наименование оператора перевода.
     * @param string|null $opinn ИНН оператора перевода.
     * @param string|null $opaddress Адрес оператора перевода.
     * @param string|null $operation Операция платежного агента.
     * @param string|null $suppliername Наименование поставщика.
     * @param string|null $supplierinn ИНН поставщика.
     * @param string|null $supplierphone Телефон поставщика, строго в формате "+7XXXXXXXXXX" или "+7-XXX-XXX-XX-XX".
     */
    public function __construct(
        string  $agentattr,
        ?string $agentphone = null,
        ?string $accopphone = null,
        ?string $opphone = null,
        ?string $opname = null,
        ?string $opinn = null,
        ?string $opaddress = null,
        ?string $operation = null,
        ?string $suppliername = null,
        ?string $supplierinn = null,
        ?string $supplierphone = null
    )
    {
        $this->agentattr = (int)$agentattr;
        $this->agentphone = $agentphone;
        $this->accopphone = $accopphone;
        $this->opphone = $opphone;
        $this->opname = $opname;
        $this->opinn = $opinn;
        $this->opaddress = $opaddress;
        $this->operation = $operation;
        $this->suppliername = $suppliername;
        $this->supplierinn = $supplierinn;
        $this->supplierphone = $supplierphone;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'agentattr' => $this->agentattr,
        ];
        if (!empty($this->agentphone)) {
            $arr['agentphone'] = $this->agentphone;
        }
        if (!empty($this->accopphone)) {
            $arr['accopphone'] = $this->accopphone;
        }
        if (!empty($this->opphone)) {
            $arr['opphone'] = $this->opphone;
        }
        if (!empty($this->opname)) {
            $arr['opname'] = $this->opname;
        }
        if (!empty($this->opinn)) {
            $arr['opinn'] = $this->opinn;
        }
        if (!empty($this->opaddress)) {
            $arr['opaddress'] = $this->opaddress;
        }
        if (!empty($this->operation)) {
            $arr['operation'] = $this->operation;
        }
        if (!empty($this->suppliername)) {
            $arr['suppliername'] = $this->suppliername;
        }
        if (!empty($this->supplierinn)) {
            $arr['supplierinn'] = $this->supplierinn;
        }
        if (!empty($this->supplierphone)) {
            $arr['supplierphone'] = $this->supplierphone;
        }
        return $arr;
    }

    /**
     * @return int
     */
    public function getAgentAttr(): int
    {
        return $this->agentattr;
    }

    /**
     * @return string|null
     */
    public function getAgentPhone(): ?string
    {
        return $this->agentphone;
    }

    /**
     * @return string|null
     */
    public function getAccOpPhone(): ?string
    {
        return $this->accopphone;
    }

    /**
     * @return string|null
     */
    public function getOpPhone(): ?string
    {
        return $this->opphone;
    }

    /**
     * @return string|null
     */
    public function getOpName(): ?string
    {
        return $this->opname;
    }

    /**
     * @return string|null
     */
    public function getOpInn(): ?string
    {
        return $this->opinn;
    }

    /**
     * @return string|null
     */
    public function getOpAddress(): ?string
    {
        return $this->opaddress;
    }

    /**
     * @return string|null
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * @return string|null
     */
    public function getSupplierName(): ?string
    {
        return $this->suppliername;
    }

    /**
     * @return string|null
     */
    public function getSupplierInn(): ?string
    {
        return $this->supplierinn;
    }

    /**
     * @return string|null
     */
    public function getSupplierPhone(): ?string
    {
        return $this->supplierphone;
    }
}
