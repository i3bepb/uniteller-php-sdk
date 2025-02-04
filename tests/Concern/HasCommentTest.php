<?php

namespace Tmconsulting\Uniteller\Tests\Concern;

use Tmconsulting\Uniteller\Concern\HasComment;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @covers \Tmconsulting\Uniteller\Concern\HasComment
 */
class HasCommentTest extends TestCase
{
    /**
     * Проверяет, что по умолчанию comment не установлен
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::getComment
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::hasComment
     */
    public function testCommentNotSetByDefault()
    {
        $object = new class {
            use HasComment;
        };

        $this->assertFalse($object->hasComment());
        $this->assertNull($object->getComment());
    }

    /**
     * Проверяет, что метод setComment() возвращает текущий объект (fluent interface)
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::setComment
     */
    public function testSetCommentReturnsSelf()
    {
        $object = new class {
            use HasComment;
        };

        $result = $object->setComment('note');

        $this->assertSame($object, $result);
    }

    /**
     * Проверяет, что метод setComment(null) сбрасывает значение
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::setComment
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::hasComment
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::getComment
     */
    public function testSetCommentNullResetsValue()
    {
        $object = new class {
            use HasComment;
        };

        $object->setComment('note');
        $this->assertTrue($object->hasComment());

        $object->setComment(null);
        $this->assertFalse($object->hasComment());
        $this->assertNull($object->getComment());
    }

    /**
     * Проверяет, что метод setComment() корректно принимает значение до 1024 символов
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::setComment
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::getComment
     */
    public function testSetCommentValidUpTo1024Characters()
    {
        $object = new class {
            use HasComment;
        };

        $comment = str_repeat('a', 1024);
        $object->setComment($comment);

        $this->assertSame($comment, $object->getComment());
    }

    /**
     * Проверяет, что метод setComment() выбрасывает исключение при передаче значения длиннее 1024 символов
     *
     * @covers \Tmconsulting\Uniteller\Concern\HasComment::setComment
     */
    public function testSetCommentTooLongThrows()
    {
        $object = new class {
            use HasComment;
        };

        $this->expectException(NotValidParameterException::class);
        $object->setComment(str_repeat('a', 1025));
    }
}
