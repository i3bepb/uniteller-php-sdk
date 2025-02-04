<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasCallbackFormat;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat
 */
class HasCallbackFormatTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию callbackFormat не установлен.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::getCallbackFormat
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::hasCallbackFormat
     */
    public function testCallbackFormatNotSetByDefault()
    {
        $object = new class {
            use HasCallbackFormat;
        };

        $this->assertFalse($object->hasCallbackFormat());
        $this->assertNull($object->getCallbackFormat());
    }

    /**
     * Проверяет, что метод setCallbackFormat() возвращает текущий объект (fluent interface).
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::setCallbackFormat
     */
    public function testSetCallbackFormatReturnsSelf()
    {
        $object = new class {
            use HasCallbackFormat;
        };
        $result = $object->setCallbackFormat('json');
        $this->assertSame($object, $result);
    }

    /**
     * Валидные значения для CallbackFormat.
     *
     * @return array
     */
    public function callbackFormatProviderValid(): array
    {
        return [
            // Единственное допустимое непустое значение
            'json' => ['json'],
        ];
    }

    /**
     * Проверяет, что метод setCallbackFormat() корректно принимает валидные значения.
     *
     * @dataProvider callbackFormatProviderValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::setCallbackFormat
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::getCallbackFormat
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::hasCallbackFormat
     */
    public function testSetValidCallbackFormat(string $value)
    {
        $object = new class {
            use HasCallbackFormat;
        };
        $object->setCallbackFormat($value);
        $this->assertTrue($object->hasCallbackFormat());
        $this->assertSame($value, $object->getCallbackFormat());
    }

    /**
     * Проверяет, что метод setCallbackFormat(null) сбрасывает значение.
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::setCallbackFormat
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::hasCallbackFormat
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::getCallbackFormat
     */
    public function testSetCallbackFormatNullResets()
    {
        $object = new class {
            use HasCallbackFormat;
        };

        $object->setCallbackFormat('json');
        $object->setCallbackFormat(null);

        $this->assertFalse($object->hasCallbackFormat());
        $this->assertNull($object->getCallbackFormat());
    }

    /**
     * Невалидные значения для CallbackFormat:
     * - пустые строки
     * - неверный регистр
     * - произвольные значения
     * - значения с пробелами
     */
    public function callbackFormatProviderInvalid(): array
    {
        return [
            // Пустая строка
            'empty string' => [''],
            // Неверный регистр
            'upper case' => ['JSON'],
            // Произвольное значение
            'xml' => ['xml'],
            // Пробел в начале
            'leading space' => [' json'],
            // Пробел в конце
            'trailing space' => ['json '],
        ];
    }
    /**
     * Проверяет невалидные значения CallbackFormat.
     *
     * @dataProvider callbackFormatProviderInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFormat::setCallbackFormat
     */
    public function testSetInvalidCallbackFormat(string $value)
    {
        $object = new class {
            use HasCallbackFormat;
        };
        $this->expectException(NotValidParameterException::class);
        $object->setCallbackFormat($value);
    }
}
