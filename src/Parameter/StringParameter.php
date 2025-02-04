<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Traits\MaxLengthValidation;
use Tmconsulting\Uniteller\Parameter\Traits\TrimmedStringValidation;

class StringParameter extends BaseParameter
{
    use TrimmedStringValidation;
    use MaxLengthValidation;

    /**
     * @var int|null Сколько разрешено символов.
     */
    private $max = null;

    /**
     * @param string $canonicalName Каноническое имя параметра внутри библиотеки.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     * @param string $unitellerName Наименнование параметра в API Uniteller.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     * @param int|null $max Сколько разрешено символов.
     * @param bool $shouldBeSent Включается ли параметр в запрос
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     */
    public function __construct(
        string $canonicalName,
        string $unitellerName,
        ?int $max = null,
        bool $shouldBeSent = true
    ) {
        parent::__construct($canonicalName, $unitellerName, $shouldBeSent);
        $this->max = $max;
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
        if (!is_string($value)) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}: must be string."
            );
        }
        $this->assertValidTrimmedString($value, $this->unitellerName);
        $this->assertMaxLength($value, $this->unitellerName, $this->max);
    }
}
