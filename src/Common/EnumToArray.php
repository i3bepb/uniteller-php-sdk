<?php

namespace Tmconsulting\Uniteller\Common;

/**
 * Помогает получить список констант класса в виде массива
 */
trait EnumToArray
{
    /**
     * Возвращает все варианты в виде массива
     *
     * @return array
     */
    public static function toArray(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }
}
