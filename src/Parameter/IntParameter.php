<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

class IntParameter extends BaseParameter
{
    /**
     * @var int|null Минимальное значение.
     */
    private $min = null;

    /**
     * @var int|null Максимальное значение.
     */
    private $max = null;

    /**
     * @param string $canonicalName Каноническое имя параметра внутри библиотеки.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     * @param string $unitellerName Наименнование параметра в API Uniteller.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     * @param int|null $min Минимальное значение.
     * @param int|null $max Максимальное значение.
     * @param bool $shouldBeSent Включается ли параметр в запрос
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     */
    public function __construct(
        string $canonicalName,
        string $unitellerName,
        ?int $min = null,
        ?int $max = null,
        bool $shouldBeSent = true
    ) {
        parent::__construct($canonicalName, $unitellerName, $shouldBeSent);
        $this->max = $max;
        $this->min = $min;
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function validate($value): void
    {
        if (!is_int($value)) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be int."
            );
        }
        if ($this->min !== null && $value < $this->min) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be greater than or equal to {$this->min}."
            );
        }
        if ($this->max !== null && $value > $this->max) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be less than or equal to {$this->max}."
            );
        }
    }
}
