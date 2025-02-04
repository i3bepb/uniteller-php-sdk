<?php

namespace Tmconsulting\Uniteller\Tests\Receipt;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Receipt\Enum\Lineattr;
use Tmconsulting\Uniteller\Receipt\Enum\Payattr;
use Tmconsulting\Uniteller\Receipt\Enum\Vat;
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\Item\Product;
use Tmconsulting\Uniteller\Receipt\Item\QtyPart;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Receipt\Item
 */
class ItemTest extends TestCase
{
    public function testCreateItem()
    {
        $product = $this->createMock(Product::class);
        $agent = $this->createMock(Agent::class);
        $qtypart = $this->createMock(QtyPart::class);

        $item = new Item(
            'Test Product',
            100.0,
            2,
            0,
            200.0,
            Vat::FIVE,
            Payattr::ADVANCE_PAYMENT,
            Lineattr::PRODUCT,
            $product,
            $agent,
            $qtypart
        );

        $data = $item->jsonSerialize();
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Test Product', $data['name']);
        $this->assertArrayHasKey('price', $data);
        $this->assertEquals(100.0, $data['price']);

        $this->assertInstanceOf(Item::class, $item);
    }

    public function testInvalidName()
    {
        $this->expectException(NotValidParameterException::class);

        new Item(
            str_repeat('A', 129),
            10,
            1,
            0,
            10,
            Vat::FREE,
            Payattr::ADVANCE_PAYMENT,
            Lineattr::PRODUCT
        );
    }

    public function testInvalidQty()
    {
        $this->expectException(NotValidParameterException::class);

        new Item(
            'Valid',
            10,
            0,
            0,
            10,
            Vat::FREE,
            Payattr::ADVANCE_PAYMENT,
            Lineattr::PRODUCT
        );
    }

    public function testInvalidVat()
    {
        $this->expectException(NotValidParameterException::class);

        new Item(
            'Valid',
            10,
            1,
            0,
            10,
            999,
            Payattr::ADVANCE_PAYMENT,
            Lineattr::PRODUCT
        );
    }

    public function testInvalidPayattr()
    {
        $this->expectException(NotValidParameterException::class);

        new Item(
            'Valid',
            10,
            1,
            0,
            10,
            Vat::FREE,
            999,
            Lineattr::PRODUCT
        );
    }

    public function testInvalidLineattr()
    {
        $this->expectException(NotValidParameterException::class);

        new Item(
            'Valid',
            10,
            1,
            0,
            10,
            Vat::FREE,
            Payattr::ADVANCE_PAYMENT,
            999
        );
    }
}
