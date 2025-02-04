<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Tests\TestCase;

class ParserCsvTest extends TestCase
{
    public function testParseSimpleCsv()
    {
        $this->assertSame([
            ['field1' => 'value1', 'field2' => 'value2']
        ], (new ParserCsv())->parse("field1,field2\nvalue1,value2"));
    }

    public function testParseEmptyCsv()
    {
        $this->assertSame([], (new ParserCsv())->parse(''));
    }

    public function testLeavesEndpointFieldsUndecoded()
    {
        $this->assertSame([
            ['ErrorCode' => '1', 'ErrorMessage' => 'Authentication error', 'Receipt' => 'not-base64']
        ], (new ParserCsv())->parse("ErrorCode,ErrorMessage,Receipt\n1,Authentication error,not-base64"));
    }
}
