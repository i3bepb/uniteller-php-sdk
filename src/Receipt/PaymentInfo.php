<?php

namespace Tmconsulting\Uniteller\Receipt;

/**
 * Информация об оплате.
 */
class PaymentInfo implements \JsonSerializable
{
    /**
     * Вид платежного средства.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Kind
     *
     * @var int
     */
    private $kind;

    /**
     * Тип дополнительного платежного средства.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\TypePaymentMethod
     *
     * @var int
     */
    private $type;

    /**
     * Идентификатор платежного средства.
     * Опциональный параметр, например, номер бонусной карты, номер подарочного сертификата, номер лицевого счета.
     *
     * @var string|null
     */
    private $id;

    /**
     * Сумма оплаты платежным средством.
     *
     * @var float
     */
    private $amount;

    /**
     * @param int $kind Вид платежного средства. See \Tmconsulting\Uniteller\Receipt\Enum\Kind.
     * @param int $type Тип дополнительного платежного средства. See \Tmconsulting\Uniteller\Receipt\Enum\TypeAdditionalPaymentMethod
     * @param float $amount Сумма оплаты платежным средством.
     * @param string|null $id Идентификатор платежного средства. Опциональный параметр, например, номер бонусной карты,
     *                        номер подарочного сертификата, номер лицевого счета.
     */
    public function __construct(int $kind, int $type, float $amount, ?string $id = null)
    {
        $this->kind = $kind;
        $this->type = $type;
        $this->amount = $amount;
        $this->id = $id;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'kind'   => $this->kind,
            'type'   => $this->type,
            'amount' => $this->amount,
            'id'     => $this->id,
        ];
    }
}
