<?php

namespace Tmconsulting\Uniteller\Tests\Dependency;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tmconsulting\Uniteller\Dependency\UnitellerContainer;
use Tmconsulting\Uniteller\Tests\TestCase;

class UnitellerContainerTest extends TestCase
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testContainsInstanceOfItself()
    {
        $uniteller = new UnitellerContainer();
        $this->assertSame($uniteller, $uniteller->get(ContainerInterface::class));
    }

    public function testNotFoundExceptionInterface()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $uniteller = new UnitellerContainer();
        $this->assertFalse($uniteller->has('NotExistClassOrId'));
        $uniteller->get('NotExistClassOrId');
    }

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function testResolved()
    {
        $anyObject = (object)[
            'name'     => 'any',
            'property' => 'example',
        ];
        $uniteller = new UnitellerContainer([], ['abc' => $anyObject]);
        $obj = $uniteller->get('abc');
        $ref = new \ReflectionObject($obj);
        $this->assertTrue($ref->hasProperty('name'));
        $this->assertTrue($ref->hasProperty('property'));
        $this->assertFalse($ref->hasProperty('foo'));
        $name = $ref->getProperty('name');
        $this->assertEquals('any', $name->getValue($obj));
        $property = $ref->getProperty('property');
        $this->assertEquals('example', $property->getValue($obj));
        /**
         * If the container is requested, it will have the current resolved dependencies.
         */
        $uniteller2 = $uniteller->get(ContainerInterface::class);
        $abc = $uniteller2->get('abc');
        $this->assertInstanceOf(\stdClass::class, $abc);
    }
}
