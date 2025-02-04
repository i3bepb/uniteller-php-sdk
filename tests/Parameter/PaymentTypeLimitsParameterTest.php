<?php

namespace Tmconsulting\Uniteller\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentType;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter;

/**
 * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter
 */
class PaymentTypeLimitsParameterTest extends TestCase
{
    /**
     * @var PaymentTypeLimitsParameter
     */
    private $parameter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parameter = new PaymentTypeLimitsParameter(
            CanonicalParameterName::PAYMENT_TYPE_LIMITS,
            UnitellerParameterName::PAYMENT_TYPE_LIMITS
        );
    }

    /**
     * Проверяет, что по умолчанию значение параметра не установлено
     *
     * @covers \Tmconsulting\Uniteller\Parameter\BaseParameter::hasValue
     * @covers \Tmconsulting\Uniteller\Parameter\BaseParameter::getValue
     */
    public function testNotSetByDefault(): void
    {
        $this->assertFalse($this->parameter->hasValue());
        $this->assertNull($this->parameter->getValue());
    }

    /**
     * Проверяет, что при передаче null значение параметра сбрасывается
     *
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::setValue
     * @covers \Tmconsulting\Uniteller\Parameter\BaseParameter::hasValue
     * @covers \Tmconsulting\Uniteller\Parameter\BaseParameter::getValue
     */
    public function testSetNullValue(): void
    {
        $this->parameter->setValue(['14' => ['100', '200']]);
        $this->parameter->setValue(null);

        $this->assertFalse($this->parameter->hasValue());
        $this->assertNull($this->parameter->getValue());
    }

    /**
     * Валидные значения PaymentTypeLimits:
     * - один тип платежа
     * - несколько типов платежей с сортировкой ключей
     * - суммы как строки без дробной части
     * - суммы как строки с 1 знаком после точки
     * - суммы как строки с 2 знаками после точки
     */
    public static function dataProviderSetValueValid(): array
    {
        return [
            'single type integer strings' => [
                ['1' => ['100', '200']],
                '{"1":[100,200]}',
            ],
            'single type decimal strings' => [
                ['1' => ['100.00', '200.50']],
                '{"1":[100.00,200.50]}',
            ],
            'single type one decimal place' => [
                ['1' => ['100.5', '0.1']],
                '{"1":[100.5,0.1]}',
            ],
            'multiple types sorted numerically' => [
                [
                    '13' => ['50.00', '50.00'],
                    '1'  => ['100.00', '100.00'],
                ],
                '{"1":[100.00,100.00],"13":[50.00,50.00]}',
            ],
            'int type is allowed' => [
                ['1' => [100, '100.00']],
                '{"1":[100,100.00]}',
            ],
        ];
    }

    /**
     * Проверяет, что метод setValue() корректно устанавливает валидные значения.
     *
     * @dataProvider dataProviderSetValueValid
     *
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::setValue
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::validate
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::encode
     * @covers \Tmconsulting\Uniteller\Parameter\BaseParameter::hasValue
     * @covers \Tmconsulting\Uniteller\Parameter\BaseParameter::getValue
     *
     * @param array $value
     * @param string $expected
     */
    public function testSetValueValid(array $value, string $expected): void
    {
        $this->parameter->setValue($value);

        $this->assertTrue($this->parameter->hasValue());
        $this->assertSame($expected, $this->parameter->getValue());
    }

    /**
     * Невалидные значения структуры:
     * - не массив
     * - пустой массив
     * - неподдерживаемый тип платежа
     * - limits не массив
     * - в limits не 2 значения
     * - отсутствует индекс 0
     * - отсутствует индекс 1
     * - строковые индексы вместо 0 и 1
     */
    public static function dataProviderSetValueInvalidStructure(): array
    {
        return [
            'not array' => [
                'invalid',
            ],
            'empty array' => [
                [],
            ],
            'unsupported payment type' => [
                ['999' => ['100.00', '100.00']],
            ],
            'limits not array' => [
                ['1' => '100.00'],
            ],
            'limits contains one value' => [
                ['1' => ['100.00']],
            ],
            'limits contains three values' => [
                ['1' => ['100.00', '100.00', '100.00']],
            ],
            'missing index 0' => [
                ['1' => [1 => '100.00', 2 => '100.00']],
            ],
            'missing index 1' => [
                ['1' => [0 => '100.00', 2 => '100.00']],
            ],
            'string indexes' => [
                ['1' => ['a' => '100.00', 'b' => '100.00']],
            ],
        ];
    }

    /**
     * Проверяет, что метод setValue() выбрасывает исключение при невалидной структуре значения.
     *
     * @dataProvider dataProviderSetValueInvalidStructure
     *
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::setValue
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::validate
     *
     * @param mixed $value
     */
    public function testSetValueInvalidStructure($value): void
    {
        $this->expectException(NotValidParameterException::class);

        $this->parameter->setValue($value);
    }

    /**
     * Невалидные суммы:
     * - отрицательное число
     * - больше двух знаков после точки
     * - запятая вместо точки
     * - пустая строка
     * - строка только из пробелов
     * - ведущие нули
     * - нестроковый тип
     */
    public static function dataProviderSetValueInvalidAmounts(): array
    {
        return [
            'negative amount' => [
                ['1' => ['-1.00', '100.00']],
            ],
            'too many decimals' => [
                ['1' => ['100.001', '100.00']],
            ],
            'comma separator' => [
                ['1' => ['100,00', '100.00']],
            ],
            'empty string' => [
                ['1' => ['', '100.00']],
            ],
            'spaces only' => [
                ['1' => ['   ', '100.00']],
            ],
            'leading zero integer' => [
                ['1' => ['01', '100.00']],
            ],
            'leading zero decimal' => [
                ['1' => ['00.50', '100.00']],
            ],
            'float type is not allowed' => [
                ['1' => [100.00, '100.00']],
            ],
            'bool type is not allowed' => [
                ['1' => [true, '100.00']],
            ],
            'zero amounts' => [
                ['1' => ['0', '0.00']],
            ],
        ];
    }

    /**
     * Проверяет, что метод setValue() выбрасывает исключение при невалидных суммах.
     *
     * @dataProvider dataProviderSetValueInvalidAmounts
     *
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::setValue
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::validate
     * @covers \Tmconsulting\Uniteller\Parameter\PaymentTypeLimitsParameter::isValidAmount
     *
     * @param array $value
     */
    public function testSetValueInvalidAmounts(array $value): void
    {
        $this->expectException(NotValidParameterException::class);

        $this->parameter->setValue($value);
    }
}
