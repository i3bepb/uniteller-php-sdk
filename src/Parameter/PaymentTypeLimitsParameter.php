<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Traits\AmountValidation;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentType;

class PaymentTypeLimitsParameter extends BaseParameter
{
    use AmountValidation;

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        if ($value === null) {
            $this->value = null;
            return;
        }
        $this->validate($value);
        $this->value = $this->encode($value);
    }

    /**
     * Ожидается массив формата:
     * [
     *     '1'  => ['100.00', '100.00'],
     *     '13' => ['100.00', '100.00'],
     * ]
     *
     * @param mixed $value
     *
     * @throws NotValidParameterException
     */
    protected function validate($value): void
    {
        if (!is_array($value) || empty($value)) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be a non-empty array."
            );
        }
        $types = PaymentType::toArray();
        foreach ($value as $paymentType => $limits) {
            if (!in_array((string)$paymentType, $types, true)) {
                throw new NotValidParameterException(
                    "Invalid {$this->unitellerName}: unsupported payment type \"{$paymentType}\". Allowed values: "
                    . implode(', ', $types) . '.'
                );
            }
            if (
                !is_array($limits)
                || count($limits) !== 2
                || !array_key_exists(0, $limits)
                || !array_key_exists(1, $limits)
            ) {
                throw new NotValidParameterException(
                    "Invalid {$this->unitellerName}: each payment type must contain exactly 2 amounts."
                );
            }
            foreach ($limits as $amount) {
                $this->assertValidAmount($this->unitellerName, $amount);
            }
        }
    }

    /**
     * Каноничная сериализация в JSON для стабильного участия в Signature.
     *
     * @param array $value
     *
     * @return string
     */
    private function encode(array $value): string
    {
        ksort($value, SORT_NUMERIC);

        $parts = [];
        foreach ($value as $paymentType => $limits) {
            $parts[] = sprintf('"%s":[%s,%s]', $paymentType, $limits[0], $limits[1]);
        }

        return '{' . implode(',', $parts) . '}';
    }
}
