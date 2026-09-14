<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Request\Format;

class FormatParameter extends BaseParameter
{
    /**
     * @var string[]
     */
    private $allowed;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param string $canonicalName Каноническое имя параметра внутри библиотеки.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     * @param string $unitellerName Наименнование параметра в API Uniteller.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     * @param string[] $allowed Строковые имена форматов, допустимых для endpoint.
     * @param string $endpoint
     * @param bool $shouldBeSent Включается ли параметр в запрос.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     */
    public function __construct(
        string $canonicalName,
        string $unitellerName,
        array $allowed,
        string $endpoint,
        bool $shouldBeSent = true
    ) {
        parent::__construct($canonicalName, $unitellerName, $shouldBeSent);
        $this->allowed = $allowed;
        $this->endpoint = $endpoint;
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
            throw new NotValidParameterException("Invalid {$this->unitellerName}: must be a format name string.");
        }
        if (!in_array($value, $this->allowed, true)
            || !array_key_exists($value, Format::getSupportedForEndpoint($this->endpoint))
        ) {
            throw new NotValidParameterException(
                "Invalid {$this->unitellerName}. Allowed values: " . implode(', ', $this->allowed) . '.'
            );
        }
    }

    /**
     * @return int
     *
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     */
    public function getValue()
    {
        return Format::resolve($this->value, $this->endpoint);
    }
}
