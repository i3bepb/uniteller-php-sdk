<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasCurrency;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\Currency;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCurrency
 */
class HasCurrencyTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию валюта не установлена
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::hasCurrency
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::getCurrency
     */
    public function testHasCurrencyReturnsFalseByDefault()
    {
        $object = new class {
            use HasCurrency;
        };

        $this->assertFalse($object->hasCurrency());
        $this->assertNull($object->getCurrency());
    }

    /**
     * Проверяет, что setCurrency() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::setCurrency
     */
    public function testSetCurrencyReturnsSelf()
    {
        $object = new class {
            use HasCurrency;
        };

        $result = $object->setCurrency(Currency::RUB);

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для Currency:
     * - пустые строки
     * - строки только из пробелов
     * - значения, не входящие в enum
     * - значения с пробелами по краям
     */
    public static function dataProviderSetCurrencyInvalid(): array
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
            // Значение, не входящее в enum
            'invalid value' => ['INVALID'],
            // Неверный регистр (если enum в верхнем регистре)
            'lower case' => ['usd'],
            // Пробел в конце строки
            'trailing space' => ['RUB '],
        ];
    }

    /**
     * Проверяет, что setCurrency() выбрасывает исключение при невалидных значениях
     *
     * @dataProvider dataProviderSetCurrencyInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::setCurrency
     */
    public function testSetCurrencyInvalid(string $val)
    {
        $object = new class {
            use HasCurrency;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setCurrency($val);
    }

    /**
     * Валидные значения для Currency:
     * - значения из enum
     */
    public static function dataProviderSetCurrencyValid(): array
    {
        return [
            // Российский рубль
            'RUB' => [Currency::RUB, Currency::RUB],
            // Доллар США
            'USD' => [Currency::USD, Currency::USD],
            // Евро
            'EUR' => [Currency::EUR, Currency::EUR],
        ];
    }

    /**
     * Проверяет, что setCurrency() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetCurrencyValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::setCurrency
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::getCurrency
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::hasCurrency
     */
    public function testSetCurrencyValid(string $input, string $expected)
    {
        $object = new class {
            use HasCurrency;
        };

        $object->setCurrency($input);

        $this->assertTrue($object->hasCurrency());
        $this->assertSame($expected, $object->getCurrency());
    }

    /**
     * Проверяет, сброс, что можно передать null
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::setCurrency
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::getCurrency
     * @covers \Tmconsulting\Uniteller\Concern\HasCurrency::hasCurrency
     */
    public function testSetCurrencyNull()
    {
        $object = new class {
            use HasCurrency;
        };

        // Установили RUB
        $object->setCurrency(Currency::RUB);
        $this->assertTrue($object->hasCurrency());
        $this->assertSame(Currency::RUB, $object->getCurrency());

        // Сбрасываем, более указывать валюты не нужно например
        $object->setCurrency(null);
        $this->assertFalse($object->hasCurrency());
        $this->assertSame(null, $object->getCurrency());
    }
}
