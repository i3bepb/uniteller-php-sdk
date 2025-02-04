<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasUrlReturn;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn
 */
class HasUrlReturnTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию URL-ы не установлены
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturnNo
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::getUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::getUrlReturnNo
     */
    public function testHasUrlReturnReturnsFalseByDefault()
    {
        $object = new class {
            use HasUrlReturn;
        };

        $this->assertFalse($object->hasUrlReturnOk());
        $this->assertFalse($object->hasUrlReturnNo());
        $this->assertNull($object->getUrlReturnOk());
        $this->assertNull($object->getUrlReturnNo());
    }

    /**
     * Проверяет, что метод setUrlReturnOk() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturnOk
     */
    public function testSetUrlReturnOkReturnsSelf()
    {
        $object = new class {
            use HasUrlReturn;
        };

        $result = $object->setUrlReturnOk('https://example.com/success');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setUrlReturnNo() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturnNo
     */
    public function testSetUrlReturnNoReturnsSelf()
    {
        $object = new class {
            use HasUrlReturn;
        };

        $result = $object->setUrlReturnNo('https://example.com/fail');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setUrlReturn() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturn
     */
    public function testSetUrlReturnReturnsSelf()
    {
        $object = new class {
            use HasUrlReturn;
        };

        $result = $object->setUrlReturn('https://example.com/return');

        $this->assertSame($object, $result);
    }

    /**
     * Невалидные значения для URL возврата:
     * - пустые строки
     * - строки только из пробелов
     * - строки длиннее 255 символов
     * - URL с кириллицей
     * - строки с пробелами по краям
     */
    public static function dataProviderSetUrlReturnInvalid(): array
    {
        return [
            'empty string'                => [''],
            'space only'                  => [' '],
            'tab only'                    => ["\t"],
            'newline only'                => ["\n"],
            'too long'                    => ['https://example.com/' . str_repeat('a', 236)],
            'cyrillic domain'             => ['https://тест.рф/success'],
            'cyrillic path'               => ['https://example.com/путь'],
            'cyrillic query'              => ['https://example.com/успех?параметр=1'],
            'leading and trailing spaces' => ['  https://example.com/return  '],
        ];
    }

    /**
     * Проверяет, что метод setUrlReturnOk() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetUrlReturnInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::validateUrlReturn
     */
    public function testSetUrlReturnOkInvalid(string $val)
    {
        $object = new class {
            use HasUrlReturn;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setUrlReturnOk($val);
    }

    /**
     * Проверяет, что метод setUrlReturnNo() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetUrlReturnInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturnNo
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::validateUrlReturn
     */
    public function testSetUrlReturnNoInvalid(string $val)
    {
        $object = new class {
            use HasUrlReturn;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setUrlReturnNo($val);
    }

    /**
     * Проверяет, что метод setUrlReturn() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderSetUrlReturnInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturn
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::validateUrlReturn
     */
    public function testSetUrlReturnInvalid(string $val)
    {
        $object = new class {
            use HasUrlReturn;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setUrlReturn($val);
    }

    /**
     * Валидные значения для URL возврата:
     * - обычные URL
     * - punycode URL
     * - строки длиной ровно 255 символов
     */
    public static function dataProviderSetUrlReturnValid(): array
    {
        $url255 = 'https://example.com/' . str_repeat('a', 235);

        return [
            'https url'       => ['https://example.com/success', 'https://example.com/success'],
            'http url'        => ['http://example.com/fail', 'http://example.com/fail'],
            'punycode domain' => ['https://xn--e1aybc.xn--p1ai/success', 'https://xn--e1aybc.xn--p1ai/success'],
            'max length 255'  => [$url255, $url255],
        ];
    }

    /**
     * Проверяет, что метод setUrlReturnOk() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetUrlReturnValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::getUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturnOk
     */
    public function testSetUrlReturnOkValid(string $input, string $expected)
    {
        $object = new class {
            use HasUrlReturn;
        };

        $object->setUrlReturnOk($input);

        $this->assertTrue($object->hasUrlReturnOk());
        $this->assertSame($expected, $object->getUrlReturnOk());
    }

    /**
     * Проверяет, что метод setUrlReturnNo() корректно принимает валидные значения
     *
     * @dataProvider dataProviderSetUrlReturnValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturnNo
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::getUrlReturnNo
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturnNo
     */
    public function testSetUrlReturnNoValid(string $input, string $expected)
    {
        $object = new class {
            use HasUrlReturn;
        };

        $object->setUrlReturnNo($input);

        $this->assertTrue($object->hasUrlReturnNo());
        $this->assertSame($expected, $object->getUrlReturnNo());
    }

    /**
     * Проверяет, что метод setUrlReturn() устанавливает один и тот же URL
     * для успешного и неуспешного возврата
     *
     * @dataProvider dataProviderSetUrlReturnValid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturnNo
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::getUrlReturnOk
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::getUrlReturnNo
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::setUrlReturn
     */
    public function testSetUrlReturnValid(string $input, string $expected)
    {
        $object = new class {
            use HasUrlReturn;
        };

        $object->setUrlReturn($input);

        $this->assertTrue($object->hasUrlReturnOk());
        $this->assertTrue($object->hasUrlReturnNo());
        $this->assertSame($expected, $object->getUrlReturnOk());
        $this->assertSame($expected, $object->getUrlReturnNo());
    }

    /**
     * Проверяет метод hasUrlReturn()
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasUrlReturn::hasUrlReturn
     */
    public function testHasUrlReturn()
    {
        $object = new class {
            use HasUrlReturn;
        };

        // по умолчанию false
        $this->assertFalse($object->hasUrlReturn());

        // только OK
        $object->setUrlReturnOk('https://example.com/success');
        $this->assertFalse($object->hasUrlReturn());

        // только NO
        $object = new class {
            use HasUrlReturn;
        };
        $object->setUrlReturnNo('https://example.com/fail');
        $this->assertFalse($object->hasUrlReturn());

        // оба установлены
        $object->setUrlReturnOk('https://example.com/success');
        $this->assertTrue($object->hasUrlReturn());
    }
}
