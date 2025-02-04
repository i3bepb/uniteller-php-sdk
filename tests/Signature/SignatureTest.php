<?php

namespace Tmconsulting\Uniteller\Tests\Signature;

use I3bepb\ReflectionForTest\AccessToProperty;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Signature\Signature
 */
class SignatureTest extends TestCase
{
    use AccessToProperty;

    /**
     * @var \Tmconsulting\Uniteller\Signature\Signature
     */
    private $signature;

    protected function setUp(): void
    {
        $this->signature = new Signature();
    }

    public function testSetFields()
    {
        $fields = ['field1' => 'value1', 'field2' => 'value2'];
        $result = $this->signature->setParameters($fields);
        $this->assertSame($this->signature, $result);
        $this->assertEquals($fields, $this->getProtectedOrPrivatePropertyValue($this->signature, 'fields'));
    }

    public function testVerifyWithValidSignature()
    {
        $fields = ['test' => 'value'];
        $this->signature->setParameters($fields);

        // Правильная подпись для массива ['test' => 'value']
        $validSignature = strtoupper(md5(implode('', $fields)));

        $this->assertTrue($this->signature->verify($validSignature));
        $this->assertFalse($this->signature->verify('fake'));
    }

    public function testVerifyWithInvalidSignature()
    {
        $this->signature->setParameters(['test' => 'value']);
        $this->assertFalse($this->signature->verify('INVALID_SIGNATURE'));
    }

    public function testCreateMd5()
    {
        $fields = ['param1' => 'value1', 'param2' => 'value2'];
        $this->signature->setParameters($fields);
        $this->assertEquals('E23DB4CAED578D98F118EF2CD703EBA0', $this->signature->createMd5());
    }

    public function testCreateMd5WithEmptyFields()
    {
        $this->signature->setParameters([]);
        $expected = strtoupper(md5(''));
        $this->assertEquals($expected, $this->signature->createMd5());
    }

    public function testCreateMd5WithoutDelimiter()
    {
        $fields = ['p1' => 'v1', 'p2' => 'v2'];
        $this->signature->setParameters($fields);
        $this->assertEquals('B29A93FDC743158B246AB456BB82EF07', $this->signature->createMd5WithoutDelimiter());
    }

    public function testCreateSha256()
    {
        $fields = ['key' => 'secret'];
        $this->signature->setParameters($fields);
        $this->assertEquals('3D91B58504A6CC3A159005EE7B16C7AE503CA6AC2A6A3C893837083C236B864A', $this->signature->createSha256());
    }

    public function testDebugSignatureCalculationWithMd5()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('Signature md5'));
        $this->signature->setLogger($logger);
        $this->signature->setDebug(true);
        $this->signature->createMd5();
    }

    public function testDebugSignatureCalculationWithSha256()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('Signature sha256'));
        $this->signature->setLogger($logger);
        $this->signature->setDebug(true);
        $this->signature->createSha256();
    }

    public function testNoDebugOutputWhenDisabled()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('debug');
        $this->signature->setLogger($logger);
        $this->signature->setDebug(false);
        $this->signature->createMd5();
    }

    public function testNullValuesHandling()
    {
        $fields = ['param1' => null, 'param2' => ''];
        $this->signature->setParameters($fields);

        $expectedMd5 = strtoupper(md5(implode('&', [md5(''),md5('')])));

        $this->assertEquals($expectedMd5, $this->signature->createMd5());
    }
}
