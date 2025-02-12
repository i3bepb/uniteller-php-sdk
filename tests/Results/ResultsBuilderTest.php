<?php

namespace Tmconsulting\Uniteller\Tests\Results;

use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Tests\TestCase;

class ResultsBuilderTest extends TestCase
{
    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdp()
    {
        $builder = new ResultsBuilder();
        $builder->setShopIdp('0009999');
        $this->assertEquals('0009999', $builder->getShopIdp());
    }

    /**
     * Set empty string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdpEmptyString()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Shop_ID empty or not set');
        $builder = new ResultsBuilder();
        $builder->setShopIdp('');
        $builder->getShopIdp();
    }

    /**
     * Not set shop id
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdpNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Shop_ID empty or not set');
        $builder = new ResultsBuilder();
        $builder->getShopIdp();
    }

    public function testGetLogin()
    {
        $builder = new ResultsBuilder();
        $builder->setLogin('0009999');
        $this->assertEquals('0009999', $builder->getShopIdp());
    }
setLogin
}