<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Request\ParserXml;
use Tmconsulting\Uniteller\Tests\TestCase;

class ParserXmlTest extends TestCase
{
    public function testParseOrdersAsArray()
    {
        $data = (new ParserXml())->parse($this->getStubContents('results'));
        $this->assertCount(2, $data['orders']['order']);
        $this->assertIsArray($data['orders']['order'][0]);
    }

    public function testLeavesEndpointFieldsUndecoded()
    {
        $data = (new ParserXml())->parse(
            '<response><Result>11</Result><ErrorMessage>Invalid ReceiptSignature</ErrorMessage>' .
            '<Receipt>not-base64</Receipt></response>'
        );
        $this->assertSame([
            'Result' => '11', 'ErrorMessage' => 'Invalid ReceiptSignature', 'Receipt' => 'not-base64'
        ], $data);
    }

    /** @dataProvider invalidXml */
    public function testInvalidXml(string $xml)
    {
        $this->expectException(InvalidResponseException::class);
        (new ParserXml())->parse($xml);
    }

    public function invalidXml(): array
    {
        return [[''], ['   '], ['<response>'], ['not XML'], ['<response><Result>0</response>']];
    }
}
