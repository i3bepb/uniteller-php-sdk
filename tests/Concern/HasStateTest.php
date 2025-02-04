<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasState;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasState
 */
class HasStateTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию State не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasState::hasState
     * @covers \Tmconsulting\Uniteller\Concern\HasState::getState
     */
    public function testNotSetByDefault()
    {
        $object = new class {
            use HasState;
        };

        $this->assertFalse($object->hasState());
        $this->assertNull($object->getState());
    }

    /**
     * Проверяет, что метод setState() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasState::setState
     */
    public function testSetReturnsSelf()
    {
        $object = new class {
            use HasState;
        };

        $this->assertSame($object, $object->setState('77'));
    }

    /**
     * Валидные значения для State:
     * - null
     * - короткие строки
     * - строка длиной 3 символа
     */
    public static function dataProviderSetStateValid(): array
    {
        return [
            'null'                => [null, null, false],
            'one char'            => ['7', '7', true],
            'two chars'           => ['77', '77', true],
            'three chars digits'  => ['123', '123', true],
            'three chars letters' => ['MOW', 'MOW', true],
        ];
    }

    /**
     * Проверяет, что метод setState() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetStateValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasState::setState
     * @covers \Tmconsulting\Uniteller\Concern\HasState::hasState
     * @covers \Tmconsulting\Uniteller\Concern\HasState::getState
     *
     * @param string|null $value
     * @param string|null $expected
     * @param bool $hasState
     */
    public function testSetStateValid($value, $expected, $hasState)
    {
        $object = new class {
            use HasState;
        };

        $object->setState($value);

        $this->assertSame($hasState, $object->hasState());
        $this->assertSame($expected, $object->getState());
    }

    /**
     * Невалидные значения для State:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 3 символов
     */
    public static function dataProviderSetStateInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' 77'],
            'trailing space'              => ['77 '],
            'leading and trailing spaces' => [' 77 '],
            'too long digits'             => ['1234'],
            'too long letters'            => ['ABCD'],
        ];
    }

    /**
     * Проверяет, что метод setState() выбрасывает исключение при передаче невалидных значений.
     *
     * @dataProvider dataProviderSetStateInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasState::setState
     *
     * @param string $value
     */
    public function testSetStateNotValid($value)
    {
        $object = new class {
            use HasState;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setState($value);
    }
}
