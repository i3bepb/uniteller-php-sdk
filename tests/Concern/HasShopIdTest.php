<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasShopId;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasShopId
 */
class HasShopIdTest extends TestCase
{
    /**
     * Проверяет, trim в setShopId
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::setShopId
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::getShopId
     */
    public function testSetShopIdSetsTrimmedValue()
    {
        $object = new class {
            use HasShopId;
        };
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage(
            'Invalid ' . UnitellerParameterName::SHOP_ID . ': must not contain leading or trailing spaces.'
        );
        $object->setShopId('  test-shop-id  ');
    }

    /**
     * Невалидные значения для Shop_ID:
     * - пустые строки
     * - строки только из пробелов
     */
    public static function dataProviderInvalid(): array
    {
        return [
            'empty string' => [''],
            'spaces only'  => ['  '],
        ];
    }

    /**
     * Проверяет, что метод setShopId() выбрасывает исключение при передаче невалидных значений
     *
     * @dataProvider dataProviderInvalid
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::setShopId
     */
    public function testSetShopIdInvalid($val)
    {
        $object = new class {
            use HasShopId;
        };
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage(
            'Invalid ' . UnitellerParameterName::SHOP_ID . ': must not be empty.'
        );
        $object->setShopId($val);
    }

    /**
     * Когда не задано изначально значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::getShopId
     */
    public function testGetShopIdThrowsExceptionWhenValueWasNotSet()
    {
        $object = new class {
            use HasShopId;
        };
        $this->assertSame('', $object->getShopId());
    }

    /**
     * Валидное значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::setShopId
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::getShopId
     */
    public function testGetShopIdReturnsPreviouslySetValue()
    {
        $object = new class {
            use HasShopId;
        };
        $object->setShopId('shop-123');
        $this->assertSame('shop-123', $object->getShopId());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::hasShopId
     */
    public function testHasShopIdReturnsTrueWhenShopIdIsEmptyInCurrentImplementation()
    {
        $object = new class {
            use HasShopId;
        };
        $this->assertFalse($object->hasShopId());
    }

    /**
     * @covers \Tmconsulting\Uniteller\Concern\HasShopId::hasShopId
     */
    public function testHasShopIdReturnsFalseWhenShopIdIsSetInCurrentImplementation()
    {
        $object = new class {
            use HasShopId;
        };
        $object->setShopId('shop-123');
        $this->assertTrue($object->hasShopId());
    }
}
