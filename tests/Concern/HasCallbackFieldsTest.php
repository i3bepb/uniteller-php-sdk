<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasCallbackFields;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields
 */
class HasCallbackFieldsTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию callbackFields не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::getCallbackFields
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::hasCallbackFields
     */
    public function testCallbackFieldsNotSetByDefault()
    {
        $object = new class {
            use HasCallbackFields;
        };

        $this->assertFalse($object->hasCallbackFields());
        $this->assertNull($object->getCallbackFields());
    }

    /**
     * Проверяет, что метод setCallbackFields() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::setCallbackFields
     */
    public function testSetCallbackFieldsReturnsSelf()
    {
        $object = new class {
            use HasCallbackFields;
        };

        $result = $object->setCallbackFields([CallbackFields::BILL_NUMBER, CallbackFields::TOTAL]);

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setCallbackFields() сортирует и объединяет значения с пробелами
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::setCallbackFields
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::getCallbackFields
     */
    public function testSetCallbackFieldsSortsAndJoinsWithSpace()
    {
        $object = new class {
            use HasCallbackFields;
        };

        $object->setCallbackFields([CallbackFields::TOTAL, CallbackFields::BILL_NUMBER]);
        $this->assertSame('BillNumber Total', $object->getCallbackFields());
    }

    /**
     * Проверяет, что метод setCallbackFields() корректно принимает значения из enum CallbackFields
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::setCallbackFields
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::getCallbackFields
     */
    public function testSetCallbackFieldsWithEnumLikeValuesSorted()
    {
        $object = new class {
            use HasCallbackFields;
        };

        $object->setCallbackFields([CallbackFields::E_MONEY_TYPE, CallbackFields::CARD_IDP]);
        $this->assertSame('Card_IDP EMoneyType', $object->getCallbackFields());
    }

    /**
     * Проверяет, что метод setCallbackFields(null) сбрасывает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::setCallbackFields
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::hasCallbackFields
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::getCallbackFields
     */
    public function testSetCallbackFieldsNullResets()
    {
        $object = new class {
            use HasCallbackFields;
        };

        $object->setCallbackFields([CallbackFields::TOTAL]);
        $this->assertTrue($object->hasCallbackFields());

        $object->setCallbackFields(null);
        $this->assertFalse($object->hasCallbackFields());
        $this->assertNull($object->getCallbackFields());
    }

    /**
     * Проверяет, что метод setCallbackFields() выбрасывает исключение при передаче значения не из enum CallbackFields
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasCallbackFields::setCallbackFields
     */
    public function testSetCallbackFieldsRejectsValueNotFromEnum()
    {
        $object = new class {
            use HasCallbackFields;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setCallbackFields([CallbackFields::TOTAL, 'field1']);
    }
}
