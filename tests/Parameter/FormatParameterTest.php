<?php

namespace Tmconsulting\Uniteller\Tests\Parameter;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CanonicalParameterName;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Parameter\FormatParameter;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Request\Format;
use Tmconsulting\Uniteller\Tests\TestCase;

class FormatParameterTest extends TestCase
{
    /** @dataProvider invalidValues */
    public function testRejectsValuesOtherThanAllowedFormatNames($value)
    {
        $parameter = $this->parameter([Format::CSV, Format::XML], ApiEndpoints::CONFIRM);
        $this->expectException(NotValidParameterException::class);
        $parameter->setValue($value);
    }

    public function invalidValues(): array
    {
        return [[1], ['1'], [1.0], [true], [[]], ['CSV'], ['unknown']];
    }

    public function testAllowedListCannotEnableAnUnsupportedEndpointFormat()
    {
        $parameter = $this->parameter([Format::CSV, Format::XML], ApiEndpoints::RECURRENT);
        $this->expectException(NotValidParameterException::class);
        $parameter->setValue(Format::XML);
    }

    public function testAllowedListCanRestrictEndpointFormats()
    {
        $parameter = $this->parameter([Format::CSV], ApiEndpoints::CONFIRM);
        $this->expectException(NotValidParameterException::class);
        $parameter->setValue(Format::XML);
    }

    public function testRejectedAssignmentPreservesPreviouslySelectedFormat()
    {
        $parameter = $this->parameter([Format::CSV, Format::XML], ApiEndpoints::CONFIRM);
        $parameter->setValue(Format::CSV);
        try {
            $parameter->setValue(Format::JSON);
            $this->fail('Expected format validation to reject JSON');
        } catch (NotValidParameterException $e) {
            $this->assertSame(1, $parameter->getValue());
        }
    }

    private function parameter(array $allowed, string $endpoint): FormatParameter
    {
        return new FormatParameter(
            CanonicalParameterName::FORMAT, UnitellerParameterName::FORMAT, $allowed, $endpoint
        );
    }
}
