<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasIData;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasIData
 */
class HasIDataTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию IData не установлено.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::hasIData
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::getIData
     */
    public function testHasIDataReturnsFalseByDefault()
    {
        $object = new class {
            use HasIData;
        };

        $this->assertFalse($object->hasIData());
        $this->assertNull($object->getIData());
    }

    /**
     * Проверяет, что метод setIData() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::setIData
     */
    public function testSetIDataReturnsSelf()
    {
        $object = new class {
            use HasIData;
        };

        $result = $object->setIData('data');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что setIData(null) сбрасывает значение.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::setIData
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::hasIData
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::getIData
     */
    public function testSetIDataNullResetsValue()
    {
        $object = new class {
            use HasIData;
        };

        $object->setIData('data');
        $this->assertTrue($object->hasIData());

        $object->setIData(null);

        $this->assertFalse($object->hasIData());
        $this->assertNull($object->getIData());
    }

    /**
     * Невалидные значения для IData:
     * - пустые строки
     * - строки только из пробелов
     * - строки с пробелами по краям
     */
    public static function dataProviderSetIDataInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'leading space'               => [' data'],
            'trailing space'              => ['data '],
            'leading and trailing spaces' => [' data '],
        ];
    }

    /**
     * Проверяет отклонение невалидных значений IData.
     *
     * @dataProvider dataProviderSetIDataInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::setIData
     */
    public function testSetIDataInvalid(string $val)
    {
        $object = new class {
            use HasIData;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setIData($val);
    }

    /**
     * Валидные значения для IData:
     * - произвольные строки
     * - JSON
     * - base64
     */
    public static function dataProviderSetIDataValid(): array
    {
        return [
            'simple string'                 => ['simple'],
            'json'                          => ['{"key":"value"}'],
            'base64'                        => [base64_encode('test')],
            'alphanumeric with underscores' => ['DATA_123_ABC'],
        ];
    }

    /**
     * Проверяет приём валидных значений IData.
     *
     * @dataProvider dataProviderSetIDataValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::setIData
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::hasIData
     * @covers \Tmconsulting\Uniteller\Concern\HasIData::getIData
     */
    public function testSetIDataValid(string $val)
    {
        $object = new class {
            use HasIData;
        };

        $object->setIData($val);

        $this->assertTrue($object->hasIData());
        $this->assertSame($val, $object->getIData());
    }
}
