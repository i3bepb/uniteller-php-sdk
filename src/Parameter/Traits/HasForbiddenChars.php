<?php

namespace Tmconsulting\Uniteller\Parameter\Traits;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

trait HasForbiddenChars
{
    /**
     * Запрещенные символы.
     *
     * @var string[]|null
     */
    protected $forbiddenChars = null;

    /**
     * Установка запрещенных символов
     *
     * @param string|string[]|null $chars Запрещенные символы.
     *
     * @return $this
     */
    public function setForbiddenChars($chars)
    {
        if ($chars === null) {
            $this->forbiddenChars = null;

            return $this;
        }
        if (is_string($chars)) {
            $chars = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($chars)) {
            throw new \InvalidArgumentException('Forbidden chars must be string, array or null.');
        }
        foreach ($chars as $char) {
            if (!is_string($char) || $char === '') {
                throw new \InvalidArgumentException('Each forbidden char must be a non-empty string.');
            }
        }
        $this->forbiddenChars = $chars;

        return $this;
    }

    /**
     * Проверка запрещенных символов.
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    protected function assertForbiddenChars(string $name, string $value): void
    {
        if ($this->forbiddenChars === null) {
            return;
        }
        foreach ($this->forbiddenChars as $char) {
            if (false !== mb_strpos($value, $char)) {
                throw new NotValidParameterException("Invalid {$name}: character \"{$char}\" is not allowed.");
            }
        }
    }
}
