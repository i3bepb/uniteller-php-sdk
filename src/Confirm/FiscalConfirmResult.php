<?php

namespace Tmconsulting\Uniteller\Confirm;

class FiscalConfirmResult
{
    /**
     * @var int Код результата ответа. {@see \Tmconsulting\Uniteller\Confirm\FiscalConfirmResultCode}
     */
    private $result;

    /**
     * @var string|null Сообщение ошибки.
     */
    private $errorMessage;

    /**
     * @var \Tmconsulting\Uniteller\Receipt\Receipt[] Массив с чеками.
     */
    private $receipts;

    /**
     * @param int $result Код результата ответа. {@see \Tmconsulting\Uniteller\Confirm\FiscalConfirmResultCode}
     * @param string|null $errorMessage Сообщение ошибки.
     * @param \Tmconsulting\Uniteller\Receipt\Receipt[] $receipts Массив с чеками.
     */
    public function __construct(int $result, ?string $errorMessage, array $receipts)
    {
        $this->result = $result;
        $this->errorMessage = $errorMessage;
        $this->receipts = $receipts;
    }

    public function getResult(): int
    {
        return $this->result;
    }

    public function isSuccess(): bool
    {
        return $this->result === FiscalConfirmResultCode::SUCCESS;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @return \Tmconsulting\Uniteller\Receipt\Receipt[]
     */
    public function getReceipts(): array
    {
        return $this->receipts;
    }
}
