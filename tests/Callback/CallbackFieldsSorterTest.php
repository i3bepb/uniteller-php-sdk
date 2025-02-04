<?php

namespace Tmconsulting\Uniteller\Tests\Callback;

use Tmconsulting\Uniteller\Callback\CallbackFieldsSorter;
use Tmconsulting\Uniteller\Parameter\CallbackFieldsParameter;
use Tmconsulting\Uniteller\Tests\TestCase;

class CallbackFieldsSorterTest extends TestCase
{
    /**
     * @dataProvider fieldsProvider
     */
    public function testCanonicalOrderAndOutgoingParameter(array $input, array $expected)
    {
        $original = $input;
        $this->assertSame($expected, CallbackFieldsSorter::sort($input));
        $this->assertSame($expected, CallbackFieldsSorter::sort(array_reverse($input)));
        $this->assertSame($expected, CallbackFieldsSorter::sort($expected));
        $this->assertSame($original, $input);

        $parameter = new CallbackFieldsParameter('callbackFields', 'CallbackFields');
        $parameter->setValue($input);
        $this->assertSame(implode(' ', $expected), $parameter->getValue());
        $parameter->setValue(null);
        $this->assertNull($parameter->getValue());
    }

    public function fieldsProvider(): array
    {
        return [
            'empty' => [[], []],
            'no fiscal fields' => [['PaymentType', 'ECI', 'AcquirerID'], ['AcquirerID', 'ECI', 'PaymentType']],
            'only total' => [['AcquirerID', 'Total'], ['Total', 'AcquirerID']],
            'no approval' => [['ECI', 'Total', 'BillNumber'], ['BillNumber', 'Total', 'ECI']],
            'duplicates' => [['Total', 'ECI', 'Total', 'ECI'], ['Total', 'ECI']],
            'all fields' => [
                ['AcquirerID', 'ApprovalCode', 'Balance', 'BillNumber', 'CardNumber', 'Card_IDP',
                    'Customer_IDP', 'ECI', 'EMoneyType', 'PaymentType', 'Total'],
                ['ApprovalCode', 'BillNumber', 'Total', 'AcquirerID', 'Balance', 'CardNumber',
                    'Card_IDP', 'Customer_IDP', 'ECI', 'EMoneyType', 'PaymentType'],
            ],
        ];
    }
}
