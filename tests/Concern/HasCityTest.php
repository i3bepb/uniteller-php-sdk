<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use PHPUnit\Framework\TestCase;
use Tmconsulting\Uniteller\Concern\HasCity;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCity
 */
class HasCityTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле City не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::hasCity
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::getCity
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasCity;
        };

        $this->assertFalse($object->hasCity());
        $this->assertNull($object->getCity());
    }

    /**
     * Проверяет, что метод setCity() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::setCity
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasCity;
        };

        $this->assertSame($object, $object->setCity('Moscow'));
    }

    /**
     * Валидные значения для City:
     * - null
     * - обычная строка
     * - строка на кириллице
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetCityValid(): array
    {
        return [
            'null'          => [null, null, false],
            'latin'         => ['Moscow', 'Moscow', true],
            'cyrillic'      => ['Екатеринбург', 'Екатеринбург', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setCity() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetCityValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::setCity
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::hasCity
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::getCity
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasCity
     */
    public function testSetCityValid($value, $expected, $hasCity)
    {
        $object = new class {
            use HasCity;
        };

        $object->setCity($value);

        $this->assertSame($hasCity, $object->hasCity());
        $this->assertSame($expected, $object->getCity());
    }

    /**
     * Невалидные значения для City:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetCityInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' Moscow'],
            'trailing space'              => ['Moscow '],
            'leading and trailing spaces' => [' Moscow '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setCity() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetCityInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCity::setCity
     *
     * @param string $value
     */
    public function testSetCityNotValid($value)
    {
        $object = new class {
            use HasCity;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setCity($value);
    }
}
