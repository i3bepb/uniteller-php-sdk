<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasFirstName;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasFirstName
 */
class HasFirstNameTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле FirstName не установлено
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::hasFirstName
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::getFirstName
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasFirstName;
        };
        $this->assertFalse($object->hasFirstName());
        $this->assertNull($object->getFirstName());
    }

    /**
     * Проверяет, что метод setFirstName() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::setFirstName
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasFirstName;
        };
        $this->assertSame($object, $object->setFirstName('A'));
    }

    /**
     * Валидные значения для FirstName:
     * - null
     * - строка на латинице
     * - строка на кириллице
     * - строка длиной ровно 64 символа
     */
    public static function dataProviderSetFirstNameValid(): array
    {
        return [
            'null'          => [null, null, false],
            'latin'         => ['Ivan', 'Ivan', true],
            'cyrillic'      => ['Иван', 'Иван', true],
            'max length 64' => [str_repeat('a', 64), str_repeat('a', 64), true],
        ];
    }

    /**
     * Проверяет, что метод setFirstName() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetFirstNameValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::setFirstName
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::hasFirstName
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::getFirstName
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasFirstName
     */
    public function testSetFirstNameValid(?string $value, ?string $expected, bool $hasFirstName)
    {
        $object = new class {
            use HasFirstName;
        };

        $object->setFirstName($value);

        $this->assertSame($hasFirstName, $object->hasFirstName());
        $this->assertSame($expected, $object->getFirstName());
    }

    /**
     * Невалидные значения для FirstName:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 64 символов
     */
    public static function dataProviderSetFirstNameInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' Ivan'],
            'trailing space'              => ['Ivan '],
            'leading and trailing spaces' => [' Ivan '],
            'too long'                    => [str_repeat('a', 65)],
        ];
    }

    /**
     * Проверяет, что метод setFirstName() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetFirstNameInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasFirstName::setFirstName
     *
     * @param string $value
     */
    public function testSetFirstNameNotValid(string $value)
    {
        $object = new class {
            use HasFirstName;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setFirstName($value);
    }
}
