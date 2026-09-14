<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use Tmconsulting\Uniteller\Exception\InvalidResponseException;
use Tmconsulting\Uniteller\Request\ParserJson;
use Tmconsulting\Uniteller\Tests\TestCase;

class ParserJsonTest extends TestCase
{
    public function testParse()
    {
        $data = (new ParserJson())->parse($this->getStubContents('responseCancelWithReceipt', 'json'));
        $this->assertSame('00', $data['Code']);
        $this->assertSame('0.00', $data['Balance']);
        $this->assertSame('canceled', $data['Status']);
        $this->assertIsString($data['Receipt']);
    }

    /** @dataProvider invalidJson */
    public function testInvalidJson(string $body)
    {
        $this->expectException(InvalidResponseException::class);
        (new ParserJson())->parse($body);
    }

    public function invalidJson(): array
    {
        return [[''], ['{'], ['null'], ['1'], ['"text"']];
    }
}
