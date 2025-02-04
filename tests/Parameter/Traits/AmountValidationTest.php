<?php

namespace Tmconsulting\Uniteller\Tests\Parameter\Traits;

use I3bepb\ReflectionForTest\AccessToMethod;
use PHPUnit\Framework\TestCase;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Traits\AmountValidation;

class AmountValidationTest extends TestCase
{
    use AccessToMethod;

    /**
     * @var object
     */
    private $objectWithAmountValidation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectWithAmountValidation = new class {
            use AmountValidation;
        };
    }

    /**
     * @return array
     */
    public function validAmountProvider(): array
    {
        return [
            'integer as string'  => ['10'],
            'integer as int'     => [10],
            'one decimal place'  => ['10.5'],
            'two decimal places' => ['10.50'],
            'less than one'      => ['0.50'],
            'small amount'       => ['0.01'],
            'large integer'      => ['999999'],
            'large decimal'      => ['999999.99'],
        ];
    }

    /**
     * @dataProvider validAmountProvider
     *
     * @param mixed $amount
     *
     * @return void
     */
    public function testAssertValidAmountAcceptsValidValues($amount): void
    {
        $this->privateMethodWithParameters(
            $this->objectWithAmountValidation,
            'assertValidAmount',
            ['Subtotal_P', $amount]
        );

        $this->assertTrue(true);
    }

    /**
     * @return array
     */
    public function invalidAmountProvider(): array
    {
        return [
            'zero as string'          => ['0'],
            'zero as int'             => [0],
            'negative integer string' => ['-1'],
            'negative decimal string' => ['-1.50'],
            'more than two decimals'  => ['10.123'],
            'leading zero integer'    => ['01'],
            'leading zero decimal'    => ['01.50'],
            'empty string'            => [''],
            'spaces only'             => ['   '],
            'comma separator'         => ['10,50'],
            'trailing dot'            => ['10.'],
            'dot only'                => ['.'],
            'non numeric'             => ['abc'],
            'alphanumeric'            => ['10a'],
            'bool true'               => [true],
            'bool false'              => [false],
            'float'                   => [10.5],
            'null'                    => [null],
            'array'                   => [[10]],
            'object'                  => [new \stdClass()],
        ];
    }

    /**
     * @dataProvider invalidAmountProvider
     *
     * @param mixed $amount
     *
     * @return void
     */
    public function testAssertValidAmountThrowsExceptionForInvalidValues($amount): void
    {
        $this->expectException(NotValidParameterException::class);

        $this->privateMethodWithParameters(
            $this->objectWithAmountValidation,
            'assertValidAmount',
            ['Subtotal_P', $amount]
        );
    }
}
