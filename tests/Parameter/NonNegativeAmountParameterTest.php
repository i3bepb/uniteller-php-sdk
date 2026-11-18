<?php

namespace Tmconsulting\Uniteller\Tests\Parameter;

use PHPUnit\Framework\TestCase;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\AmountParameter;
use Tmconsulting\Uniteller\Parameter\NonNegativeAmountParameter;

class NonNegativeAmountParameterTest extends TestCase
{
    /** @dataProvider validAmounts */
    public function testAcceptsAmountAndPreservesItsRepresentation($amount): void
    {
        $parameter = new NonNegativeAmountParameter('Subtotal', 'Subtotal');
        $parameter->setValue($amount);

        $this->assertTrue($parameter->hasValue());
        $this->assertSame($amount, $parameter->getValue());
    }

    public function validAmounts(): array
    {
        return [
            [0], ['0'], ['0.0'], ['0.00'], [20], ['20.50'], ['0.01'],
            ['999999999999999999999.99'],
        ];
    }

    /** @dataProvider invalidAmounts */
    public function testRejectsInvalidAmount($amount): void
    {
        $parameter = new NonNegativeAmountParameter('Subtotal', 'Subtotal');
        $this->expectException(NotValidParameterException::class);
        $parameter->setValue($amount);
    }

    public function invalidAmounts(): array
    {
        return [
            [-1], ['-0.01'], ['-0'], ['-0.00'], [1.23], ['1.234'], ['1,23'],
            ['1e2'], ['+1'], ['01'], [' 1 '], ["1\n"], [''], [true], [[]],
        ];
    }

    /** @dataProvider zeroAmounts */
    public function testPositiveAmountParameterStillRejectsZero($amount): void
    {
        $parameter = new AmountParameter('Subtotal', 'Subtotal');
        $this->expectException(NotValidParameterException::class);
        $parameter->setValue($amount);
    }

    public function zeroAmounts(): array
    {
        return [[0], ['0'], ['0.0'], ['0.00']];
    }
}
