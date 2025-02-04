<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Receipt\Params;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Params
 */
class ParamsTest extends TestCase
{
    public function testJsonSerialize()
    {
        $place = 'https://example.com/place';
        $params = new Params($place);
        $this->assertInstanceOf(Params::class, $params);
        $json = $params->jsonSerialize();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('place', $json);
        $this->assertEquals($place, $json['place']);
    }
}
