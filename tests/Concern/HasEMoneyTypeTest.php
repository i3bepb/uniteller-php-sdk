<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasEMoneyType;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\EMoneyType;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType
 */
class HasEMoneyTypeTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию поле EMoneyType не установлено
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::hasEMoneyType
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::getEMoneyType
     */
    public function testHasEMoneyTypeReturnsFalseByDefault()
    {
        $object = new class {
            use HasEMoneyType;
        };

        $this->assertFalse($object->hasEMoneyType());
        $this->assertNull($object->getEMoneyType());
    }

    /**
     * Проверяет, что метод setEMoneyType() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::setEMoneyType
     */
    public function testSetEMoneyTypeReturnsSelf()
    {
        $object = new class {
            use HasEMoneyType;
        };
        $result = $object->setEMoneyType(EMoneyType::YANDEX_MONEY);

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setEMoneyType() принимает null:
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::hasEMoneyType
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::getEMoneyType
     */
    public function testSetEMoneyTypeAcceptsNull()
    {
        $object = new class {
            use HasEMoneyType;
        };
        $object->setEMoneyType(null);

        $this->assertFalse($object->hasEMoneyType());
        $this->assertNull($object->getEMoneyType());
    }

    /**
     * Невалидные значения для EMoneyType:
     * - значения вне допустимого диапазона
     */
    public static function dataProviderSetEMoneyTypeInvalid(): array
    {
        return [
            // Отрицательное значение
            'negative' => [-1],
            // Значение ниже допустимого диапазона
            'below range' => [2],
            // Значение в разрыве допустимых значений
            'gap value 17' => [17],
            // Значение в разрыве допустимых значений
            'gap value 30' => [30],
            // Значение значительно выше диапазона
            'above range' => [100],
        ];
    }

    /**
     * Проверяет, что метод setEMoneyType() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetEMoneyTypeInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::setEMoneyType
     */
    public function testSetEMoneyTypeInvalid($val)
    {
        $object = new class {
            use HasEMoneyType;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setEMoneyType($val);
    }

    /**
     * Валидные значения для EMoneyType:
     * - null
     * - все допустимые значения из enum EMoneyType
     */
    public static function dataProviderSetEMoneyTypeValid(): array
    {
        return [
            // Значение не задано
            'null' => [null],
            // Любой тип (ANY)
            'any' => [EMoneyType::ANY],
            // Яндекс.Деньги
            'yandex money' => [EMoneyType::YANDEX_MONEY],
            // Наличные
            'cash' => [EMoneyType::CASH],
            // QIWI REST
            'qiwi rest' => [EMoneyType::QIWI_REST],
            // Mobi Money
            'mobi money' => [EMoneyType::MOBI_MONEY],
            // WebMoney WMR
            'webmoney wmr' => [EMoneyType::WEBMONEY_WMR],
        ];
    }

    /**
     * Проверяет, что метод setEMoneyType() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetEMoneyTypeValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::hasEMoneyType
     * @covers \Tmconsulting\Uniteller\Concern\HasEMoneyType::getEMoneyType
     */
    public function testSetEMoneyTypeValid($val)
    {
        $object = new class {
            use HasEMoneyType;
        };
        $object->setEMoneyType($val);

        $this->assertSame($val, $object->getEMoneyType());
        $this->assertSame($val !== null, $object->hasEMoneyType());
    }
}
