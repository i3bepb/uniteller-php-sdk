<?php

namespace Tmconsulting\Uniteller\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Request\Format;

/**
 * Принимает строковое имя формата и преобразует его в значение API для выбранного метода.
 */
class FormatParameter extends BaseParameter
{
    /**
     * @var string[] Допустимые строковые имена форматов.
     */
    private $allowed;

    /**
     * @var string Адрес метода API, определяющий кодировку формата.
     */
    private $endpoint;

    /**
     * @param string $canonicalName Каноническое имя параметра внутри библиотеки.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     * @param string $unitellerName Наименнование параметра в API Uniteller.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     * @param string[] $allowed Имена форматов из ключей Format::getSupportedForEndpoint().
     * @param string $endpoint Адрес метода API.
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
     * Проверяет, что строковое имя формата разрешено параметром и поддерживается методом API.
     * Числовые коды форматов API и их строковые записи не принимаются.
     *
     * @param mixed $value Проверяемое имя формата.
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
     * @return int Код выбранного строкового формата для метода API.
     *
     * @throws \Tmconsulting\Uniteller\Exception\FormatNotSupportedException
     */
    public function getValue()
    {
        return Format::resolve($this->value, $this->endpoint);
    }
}
