<?php

namespace Tmconsulting\Uniteller\Confirm;

/**
 * Результат запроса подтверждения: код результат, сообщение ошибки и полученные фискальные чеки.
 */
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
     * @var \Tmconsulting\Uniteller\Receipt\FiscalReceipt[] Массив с чеками.
     */
    private $receipts;

    /**
     * @param int $result Код результата ответа. {@see \Tmconsulting\Uniteller\Confirm\FiscalConfirmResultCode}
     * @param string|null $errorMessage Сообщение ошибки.
     * @param \Tmconsulting\Uniteller\Receipt\FiscalReceipt[] $receipts Массив с чеками.
     */
    public function __construct(int $result, ?string $errorMessage, array $receipts)
    {
        $this->result = $result;
        $this->errorMessage = $errorMessage;
        $this->receipts = $receipts;
    }

    /**
     * @return int Код Result из ответа. {@see \Tmconsulting\Uniteller\Confirm\FiscalConfirmResultCode}
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @return bool Соответствует ли код результата успешному подтверждению.
     */
    public function isSuccess(): bool
    {
        return $this->result === FiscalConfirmResultCode::SUCCESS;
    }

    /**
     * @return string|null Сообщение ошибки, null при отсутствии поля ErrorMessage в ответе.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @return \Tmconsulting\Uniteller\Receipt\FiscalReceipt[] Фискальные чеки, пустой массив, если Receipt отсутствовал.
     */
    public function getReceipts(): array
    {
        return $this->receipts;
    }
}
