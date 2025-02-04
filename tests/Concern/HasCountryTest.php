<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use PHPUnit\Framework\TestCase;
use Tmconsulting\Uniteller\Concern\HasCountry;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCountry
 */
class HasCountryTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле Country не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::hasCountry
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::getCountry
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasCountry;
        };

        $this->assertFalse($object->hasCountry());
        $this->assertNull($object->getCountry());
    }

    /**
     * Проверяет, что метод setCountry() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::setCountry
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasCountry;
        };

        $this->assertSame($object, $object->setCountry('Russia'));
    }

    /**
     * Валидные значения для Country:
     * - null
     * - обычная строка
     * - строка на кириллице
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetCountryValid(): array
    {
        return [
            'null'          => [null, null, false],
            'latin'         => ['Russia', 'Russia', true],
            'cyrillic'      => ['Россия', 'Россия', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setCountry() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetCountryValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::setCountry
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::hasCountry
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::getCountry
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasCountry
     */
    public function testSetCountryValid($value, $expected, $hasCountry)
    {
        $object = new class {
            use HasCountry;
        };

        $object->setCountry($value);

        $this->assertSame($hasCountry, $object->hasCountry());
        $this->assertSame($expected, $object->getCountry());
    }

    /**
     * Невалидные значения для Country:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetCountryInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' Russia'],
            'trailing space'              => ['Russia '],
            'leading and trailing spaces' => [' Russia '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setCountry() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetCountryInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCountry::setCountry
     *
     * @param string $value
     */
    public function testSetCountryNotValid($value)
    {
        $object = new class {
            use HasCountry;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setCountry($value);
    }
}
