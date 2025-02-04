<?php

namespace Tmconsulting\Uniteller\Parameter;

abstract class BaseParameter implements ParameterInterface
{
    /**
     * @var string Каноническое имя параметра внутри библиотеки.
     *             Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     */
    protected $canonicalName;

    /**
     * @var string Наименнование параметра в API Uniteller.
     *             Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     */
    protected $unitellerName;

    /**
     * @var string|int|null Значение параметра.
     */
    protected $value = null;

    /**
     * @var bool Включается ли параметр в запрос
     */
    protected $shouldBeSent = true;

    /**
     * @param string $canonicalName Каноническое имя параметра внутри библиотеки.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     * @param string $unitellerName Наименнование параметра в API Uniteller.
     *                              Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     * @param bool $shouldBeSent    Включается ли параметр в запрос
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     */
    public function __construct(string $canonicalName, string $unitellerName, bool $shouldBeSent = true)
    {
        $this->canonicalName = $canonicalName;
        $this->unitellerName = $unitellerName;
        $this->shouldBeSent = $shouldBeSent;
    }

    /**
     * @return string Наименнование параметра в API Uniteller.
     *                Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     */
    public function getUnitellerName(): string
    {
        return $this->unitellerName;
    }

    /**
     * @return string Каноническое имя параметра внутри этой библиотеки.
     *                Одно из \Tmconsulting\Uniteller\Builder\Enum\CanonicalParameterName.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     */
    public function getCanonicalName(): string
    {
        return $this->canonicalName;
    }

    /**
     * Включается ли параметр в запрос
     *
     * @return bool
     */
    public function isShouldBeSent(): bool
    {
        return $this->shouldBeSent;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Установлено ли значение.
     *
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }

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
        $this->value = $value;
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    abstract protected function validate($value): void;
}
