<?php

namespace Tmconsulting\Uniteller\Parameter;

interface ParameterInterface
{
    /**
     * @return string Наименнование параметра.
     *                Одно из \Tmconsulting\Uniteller\Builder\Enum\UnitellerParameterName.
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName
     */
    public function getUnitellerName(): string;

    /**
     * @return string Каноническое имя параметра внутри этой библиотеки.
     *                Одно из \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     *
     * @see \Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName
     */
    public function getCanonicalName(): string;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * Установлено ли значение.
     *
     * @return bool
     */
    public function hasValue(): bool;
}
