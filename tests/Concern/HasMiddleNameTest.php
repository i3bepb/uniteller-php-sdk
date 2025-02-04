<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasMiddleName;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName
 */
class HasMiddleNameTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле MiddleName не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::hasMiddleName
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::getMiddleName
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasMiddleName;
        };

        $this->assertFalse($object->hasMiddleName());
        $this->assertNull($object->getMiddleName());
    }

    /**
     * Проверяет, что метод setMiddleName() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::setMiddleName
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasMiddleName;
        };

        $this->assertSame($object, $object->setMiddleName('Petrovich'));
    }

    /**
     * Валидные значения для MiddleName:
     * - null
     * - строка на латинице
     * - строка на кириллице
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetMiddleNameValid(): array
    {
        return [
            'null'          => [null, null, false],
            'latin'         => ['Ivanovich', 'Ivanovich', true],
            'cyrillic'      => ['Иванович', 'Иванович', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setMiddleName() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetMiddleNameValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::setMiddleName
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::hasMiddleName
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::getMiddleName
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasMiddleName
     */
    public function testSetMiddleNameValid($value, $expected, $hasMiddleName)
    {
        $object = new class {
            use HasMiddleName;
        };

        $object->setMiddleName($value);

        $this->assertSame($hasMiddleName, $object->hasMiddleName());
        $this->assertSame($expected, $object->getMiddleName());
    }

    /**
     * Невалидные значения для MiddleName:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetMiddleNameInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' Иванович'],
            'trailing space'              => ['Иванович '],
            'leading and trailing spaces' => [' Иванович '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setMiddleName() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetMiddleNameInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasMiddleName::setMiddleName
     *
     * @param string $value
     */
    public function testSetMiddleNameNotValid($value)
    {
        $object = new class {
            use HasMiddleName;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setMiddleName($value);
    }
}
