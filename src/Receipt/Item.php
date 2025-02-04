<?php

namespace Tmconsulting\Uniteller\Receipt;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Receipt\Enum\Lineattr;
use Tmconsulting\Uniteller\Receipt\Enum\Payattr;
use Tmconsulting\Uniteller\Receipt\Enum\Vat;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\Item\Product;
use Tmconsulting\Uniteller\Receipt\Item\QtyPart;

/**
 * Описание позиции.
 */
class Item implements \JsonSerializable
{
    /**
     * Наименование позиции.
     * Максимально 128 символов.
     *
     * @var string
     */
    private $name;

    /**
     * Цена за единицу измерения.
     *
     * @var float
     */
    private $price;

    /**
     * Количество. Не может иметь нулевое значение.
     *
     * @var int
     */
    private $qty = 0;

    /**
     * Дробное количество маркированного товара. Доступно только для товаров с маркировкой.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item\QtyPart|null
     */
    private $qtypart;

    /**
     * Код меры количества предмета расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Unit
     *
     * @var int
     */
    private $unit;

    /**
     * Сумма.
     *
     * @var float
     */
    private $sum = 0;

    /**
     * Код значения ставки НДС.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Vat
     *
     * @var int
     */
    private $vat = Vat::FREE;

    /**
     * Признак способа расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Payattr
     *
     * @var int
     */
    private $payattr;

    /**
     * Признак предмета расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Lineattr
     *
     * @var int
     */
    private $lineattr;

    /**
     * Дополнительные сведения о продукте.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item\Product|null
     */
    private $product;

    /**
     * Данные агента.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item\Agent|null
     */
    private $agent;

    /**
     * @param string $name Наименование позиции. Максимально 128 символов.
     * @param string|float|int $price Цена за единицу измерения.
     * @param int $qty Количество. Не может иметь нулевое значение.
     * @param int $unit Код меры количества предмета расчета. See \Tmconsulting\Uniteller\Receipt\Enum\Unit.
     * @param string|float|int $sum Сумма.
     * @param int $vat Код значения ставки НДС. See \Tmconsulting\Uniteller\Receipt\Enum\Vat.
     * @param int $payattr Признак способа расчета. See \Tmconsulting\Uniteller\Receipt\Enum\Payattr.
     * @param int $lineattr Признак предмета расчета. See \Tmconsulting\Uniteller\Receipt\Enum\Lineattr.
     * @param \Tmconsulting\Uniteller\Receipt\Item\Product|null $product Дополнительные сведения о продукте.
     * @param \Tmconsulting\Uniteller\Receipt\Item\Agent|null $agent Данные агента.
     * @param \Tmconsulting\Uniteller\Receipt\Item\QtyPart|null $qtypart Дробное количество маркированного товара.
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function __construct(
        string   $name,
                 $price,
        int      $qty,
        int      $unit,
                 $sum,
        int      $vat,
        int      $payattr,
        int      $lineattr,
        ?Product $product = null,
        ?Agent   $agent = null,
        ?QtyPart $qtypart = null
    )
    {
        $this->setName($name);
        $this->price = (float)$price;
        $this->setQty($qty);
        $this->qtypart = $qtypart;
        $this->unit = $unit;
        $this->sum = (float)$sum;
        $this->setVat($vat);
        $this->setPayattr($payattr);
        $this->setLineattr($lineattr);
        $this->product = $product;
        $this->agent = $agent;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'name'     => $this->name,
            'price'    => $this->price,
            'qty'      => $this->qty,
            'qtypart'  => $this->qtypart,
            'unit'     => $this->unit,
            'sum'      => $this->sum,
            'vat'      => $this->vat,
            'payattr'  => $this->payattr,
            'lineattr' => $this->lineattr,
        ];
        if (!empty($this->product)) {
            $arr['product'] = $this->product;
        }
        if (!empty($this->agent)) {
            $arr['agent'] = $this->agent;
        }
        return $arr;
    }

    /**
     * @param string $name
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function setName(string $name)
    {
        if (mb_strlen($name) > 128) {
            throw new NotValidParameterException('Not valid parameter name, max 128 chars');
        }
        $this->name = $name;
    }

    /**
     * @param int $qty
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function setQty(int $qty)
    {
        if ($qty <= 0) {
            throw new NotValidParameterException('Not valid parameter qty, must be > 0');
        }
        $this->qty = $qty;
    }

    /**
     * @param int $vat
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function setVat(int $vat)
    {
        $vats = Vat::toArray();
        if (!in_array($vat, $vats, true)) {
            throw new NotValidParameterException(
                'Not valid parameter vat, must be one of the values: ' . implode(',', $vats)
            );
        }
        $this->vat = $vat;
    }

    /**
     * @param int $payattr
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    protected function setPayattr(int $payattr)
    {
        $pays = Payattr::toArray();
        if (!in_array($payattr, $pays, true)) {
            throw new NotValidParameterException(
                'Not valid parameter payattr, must be one of the values: ' . implode(',', $pays)
            );
        }
        $this->payattr = $payattr;
    }

    /**
     * @param int $lineattr
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setLineattr(int $lineattr): void
    {
        $lineattrs = Lineattr::toArray();
        if (!in_array($lineattr, $lineattrs, true)) {
            throw new NotValidParameterException(
                'Not valid parameter lineattr, must be one of the values: ' . implode(',', $lineattrs)
            );
        }
        $this->lineattr = $lineattr;
    }

    /**
     * @return \Tmconsulting\Uniteller\Receipt\Item\Agent|null
     */
    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

}
