<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasLastName;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasLastName
 */
class HasLastNameTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле LastName не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::hasLastName
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::getLastName
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasLastName;
        };

        $this->assertFalse($object->hasLastName());
        $this->assertNull($object->getLastName());
    }

    /**
     * Проверяет, что метод setLastName() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::setLastName
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasLastName;
        };

        $this->assertSame($object, $object->setLastName('Ivanov'));
    }

    /**
     * Валидные значения для LastName:
     * - null
     * - строка на латинице
     * - строка на кириллице
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetLastNameValid(): array
    {
        return [
            'null'          => [null, null, false],
            'latin'         => ['Ivanov', 'Ivanov', true],
            'cyrillic'      => ['Иванов', 'Иванов', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setLastName() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetLastNameValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::setLastName
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::hasLastName
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::getLastName
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasLastName
     */
    public function testSetLastNameValid($value, $expected, $hasLastName)
    {
        $object = new class {
            use HasLastName;
        };

        $object->setLastName($value);

        $this->assertSame($hasLastName, $object->hasLastName());
        $this->assertSame($expected, $object->getLastName());
    }

    /**
     * Невалидные значения для LastName:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetLastNameInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' Ivanov'],
            'trailing space'              => ['Ivanov '],
            'leading and trailing spaces' => [' Ivanov '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setLastName() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetLastNameInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasLastName::setLastName
     *
     * @param string $value
     */
    public function testSetLastNameNotValid($value)
    {
        $object = new class {
            use HasLastName;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setLastName($value);
    }
}
