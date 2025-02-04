<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasCardIdp;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp
 */
class HasCardIdpTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию Card_IDP не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::hasCardIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::getCardIdp
     */
    public function testHasCardIdpReturnsFalseByDefault()
    {
        $object = new class {
            use HasCardIdp;
        };

        $this->assertFalse($object->hasCardIdp());
        $this->assertNull($object->getCardIdp());
    }

    /**
     * Проверяет, что метод setCardIdp() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::setCardIdp
     */
    public function testSetCardIdpReturnsSelf()
    {
        $object = new class {
            use HasCardIdp;
        };

        $result = $object->setCardIdp('card-123');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что setCardIdp(null) сбрасывает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::setCardIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::hasCardIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::getCardIdp
     */
    public function testSetCardIdpNullResetsValue()
    {
        $object = new class {
            use HasCardIdp;
        };

        $object->setCardIdp('card-123');
        $this->assertTrue($object->hasCardIdp());

        $object->setCardIdp(null);
        $this->assertFalse($object->hasCardIdp());
        $this->assertNull($object->getCardIdp());
    }

    /**
     * Невалидные значения для Card_IDP:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     * - строки длиннее 128 символов
     */
    public static function dataProviderSetCardIdpInvalid(): array
    {
        return [
            // Пустая строка
            'empty string' => [''],
            // Строка из пробела
            'space only' => [' '],
            // Табуляция
            'tab only' => ["\t"],
            // Перевод строки
            'newline only' => ["\n"],
            // Пробел в начале
            'leading space' => [' card-123'],
            // Пробел в конце
            'trailing space' => ['card-123 '],
            // Пробелы по краям
            'leading and trailing spaces' => [' card-123 '],
            // Превышение максимальной длины (129 > 128)
            'too long' => [str_repeat('a', 129)],
        ];
    }

    /**
     * Проверяет, что метод setCardIdp() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetCardIdpInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::setCardIdp
     */
    public function testSetCardIdpNotValid($val)
    {
        $object = new class {
            use HasCardIdp;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setCardIdp($val);
    }

    /**
     * Валидные значения для Card_IDP:
     * - обычные строки
     * - строки с различными допустимыми символами
     * - строки длиной до 128 символов
     */
    public static function dataProviderSetCardIdpValid(): array
    {
        return [
            // Обычная строка
            'simple value' => ['card-123'],
            // Строка с заглавными буквами и подчёркиваниями
            'upper case with underscores' => ['CARD_ID_ABC_123'],
            // Строка в формате токена
            'token format' => ['token_abcdef'],
            // Граничное значение: ровно 128 символов
            'max length 128' => [str_repeat('a', 128)],
        ];
    }

    /**
     * Проверяет, что метод setCardIdp() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetCardIdpValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::setCardIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::hasCardIdp
     * @covers \Tmconsulting\Uniteller\Concern\HasCardIdp::getCardIdp
     */
    public function testSetCardIdpValid(string $val)
    {
        $object = new class {
            use HasCardIdp;
        };

        $object->setCardIdp($val);

        $this->assertTrue($object->hasCardIdp());
        $this->assertSame($val, $object->getCardIdp());
    }
}
