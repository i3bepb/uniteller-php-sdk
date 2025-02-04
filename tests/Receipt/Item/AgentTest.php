<?php

namespace Tmconsulting\Uniteller\Tests\Receipt\Item;

use Tmconsulting\Uniteller\Receipt\Enum\AgentAttribute;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Item\Agent
 */
class AgentTest extends TestCase
{
    public function testJsonSerializeWithOnlyRequiredField()
    {
        $agent = new Agent(AgentAttribute::AGENT);

        $expected = [
            'agentattr' => AgentAttribute::AGENT,
        ];

        $this->assertSame($expected, $agent->jsonSerialize());
    }

    public function testJsonSerializeWithAllFields()
    {
        $agent = new Agent(
            AgentAttribute::AGENT,
            '+71234567890',
            '+71234567891',
            '+71234567892',
            'Operator Name',
            '1234567890',
            'Operator Address',
            'Payment operation',
            'Supplier Ltd',
            '0987654321',
            '+7-123-456-78-90'
        );

        $expected = [
            'agentattr'     => AgentAttribute::AGENT,
            'agentphone'    => '+71234567890',
            'accopphone'    => '+71234567891',
            'opphone'       => '+71234567892',
            'opname'        => 'Operator Name',
            'opinn'         => '1234567890',
            'opaddress'     => 'Operator Address',
            'operation'     => 'Payment operation',
            'suppliername'  => 'Supplier Ltd',
            'supplierinn'   => '0987654321',
            'supplierphone' => '+7-123-456-78-90',
        ];

        $this->assertSame($expected, $agent->jsonSerialize());
    }

    public function testJsonEncodeMatchesExpectedJson()
    {
        $agent = new Agent(
            AgentAttribute::AGENT,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            '+71234567890'
        );

        $expectedJson = json_encode([
            'agentattr'     => AgentAttribute::AGENT,
            'supplierphone' => '+71234567890',
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($agent));
    }
}
