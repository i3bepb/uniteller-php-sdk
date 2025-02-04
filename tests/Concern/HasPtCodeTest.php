<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasPtCode;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasPtCode
 */
class HasPtCodeTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию PT_Code не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::hasPtCode
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::getPtCode
     */
    public function testHasPtCodeReturnsFalseByDefault()
    {
        $object = new class {
            use HasPtCode;
        };

        $this->assertFalse($object->hasPtCode());
        $this->assertNull($object->getPtCode());
    }

    /**
     * Проверяет, что метод setPtCode() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::setPtCode
     */
    public function testSetPtCodeReturnsSelf()
    {
        $object = new class {
            use HasPtCode;
        };

        $result = $object->setPtCode('PAYMENT');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что setPtCode(null) сбрасывает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::setPtCode
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::hasPtCode
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::getPtCode
     */
    public function testSetPtCodeNullResetsValue()
    {
        $object = new class {
            use HasPtCode;
        };

        $object->setPtCode('PAYMENT');
        $this->assertTrue($object->hasPtCode());
        $this->assertSame('PAYMENT', $object->getPtCode());

        $object->setPtCode(null);
        $this->assertFalse($object->hasPtCode());
        $this->assertNull($object->getPtCode());
    }

    /**
     * Невалидные значения для PT_Code:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 10 символов
     */
    public static function dataProviderSetPtCodeInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' PAYMENT'],
            'trailing space'              => ['PAYMENT '],
            'leading and trailing spaces' => [' PAYMENT '],
            'too long'                    => ['12345678901'],
        ];
    }

    /**
     * Проверяет, что метод setPtCode() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetPtCodeInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::setPtCode
     */
    public function testSetPtCodeInvalid(string $val)
    {
        $object = new class {
            use HasPtCode;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setPtCode($val);
    }

    /**
     * Валидные значения для PT_Code:
     * - обычные строки
     * - строки с цифрами и спецсимволами
     * - строки длиной до 10 символов
     */
    public static function dataProviderSetPtCodeValid(): array
    {
        return [
            'single char'                => ['A', 'A'],
            'simple string'              => ['PAYMENT', 'PAYMENT'],
            'with underscore and digits' => ['PAY_01', 'PAY_01'],
            'max length 10'              => ['1234567890', '1234567890'],
            'with special characters'    => ['A-B_C.1', 'A-B_C.1'],
        ];
    }

    /**
     * Проверяет, что метод setPtCode() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetPtCodeValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::setPtCode
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::hasPtCode
     * @covers \Tmconsulting\Uniteller\Concern\HasPtCode::getPtCode
     */
    public function testSetPtCodeValid(string $input, string $expected)
    {
        $object = new class {
            use HasPtCode;
        };

        $object->setPtCode($input);

        $this->assertTrue($object->hasPtCode());
        $this->assertSame($expected, $object->getPtCode());
    }
}
