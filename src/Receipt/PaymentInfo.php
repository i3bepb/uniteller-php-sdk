<?php

namespace Tmconsulting\Uniteller\Receipt;

use Tmconsulting\Uniteller\Support\RawDataAwareTrait;

/**
 * Информация об оплате.
 */
class PaymentInfo implements \JsonSerializable
{
    use RawDataAwareTrait;

    /**
     * Вид платежного средства.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Kind
     *
     * @var int
     */
    protected $kind;

    /**
     * Тип дополнительного платежного средства.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod
     *
     * @var int
     */
    protected $type;

    /**
     * Идентификатор платежного средства.
     *
     * Опциональный параметр, например, номер бонусной карты, номер подарочного сертификата или номер лицевого счета.
     *
     * @var string|null
     */
    protected $id;

    /**
     * Сумма оплаты платежным средством.
     *
     * @var string
     */
    protected $amount;

    /**
     * @param int $kind Вид платежного средства. См. \Tmconsulting\Uniteller\Receipt\Enum\Kind.
     * @param int $type Тип дополнительного платежного средства. См. \Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod.
     * @param string $amount Сумма оплаты платежным средством.
     * @param string|null $id Идентификатор платежного средства, например, номер бонусной карты, подарочного
     *                        сертификата или лицевого счета.
     * @param array $rawData Исходные данные, т.е. ассоциативный массив, который получился из json.
     *
     * @throws \ReflectionException
     */
    public function __construct(
        int     $kind,
        int     $type,
        string  $amount,
        ?string $id = null,
        array   $rawData = []
    ) {
        $this->kind = $kind;
        $this->type = $type;
        $this->amount = $amount;
        $this->id = $id;
        $this->setRawData($rawData);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'kind'   => $this->kind,
            'type'   => $this->type,
            'amount' => $this->amount,
        ];
        if ($this->id !== null) {
            $arr['id'] = $this->id;
        }
        return $arr;
    }

    /**
     * Возвращает вид платежного средства.
     *
     * @return int|null
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Kind
     */
    public function getKind(): ?int
    {
        return $this->kind;
    }

    /**
     * Возвращает тип дополнительного платежного средства.
     *
     * @return int|null
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * Возвращает идентификатор платежного средства.
     *
     * Например, номер бонусной карты, подарочного сертификата или лицевого счета.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Возвращает сумму оплаты платежным средством.
     *
     * @return string|null
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }
}
