<?php

namespace Tmconsulting\Uniteller\Receipt;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Receipt\Enum\Lineattr;
use Tmconsulting\Uniteller\Receipt\Enum\Payattr;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\Item\Product;
use Tmconsulting\Uniteller\Receipt\Item\QtyPart;
use Tmconsulting\Uniteller\Support\RawDataAwareTrait;

/**
 * Описание позиции.
 */
class Item implements \JsonSerializable
{
    use RawDataAwareTrait;

    /**
     * Наименование позиции.
     * Максимально 128 символов.
     *
     * @var string
     */
    protected $name;

    /**
     * Цена за единицу измерения.
     *
     * @var string
     */
    protected $price;

    /**
     * Количество. Не может иметь нулевое значение.
     *
     * @var int
     */
    protected $qty;

    /**
     * Дробное количество маркированного товара. Доступно только для товаров с маркировкой.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item\QtyPart|null
     */
    protected $qtypart;

    /**
     * Код меры количества предмета расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Unit
     *
     * @var int
     */
    protected $unit;

    /**
     * Сумма.
     *
     * @var string
     */
    protected $sum;

    /**
     * Код значения ставки НДС.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Vat
     *
     * @var int
     */
    protected $vat;

    /**
     * Признак способа расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Payattr
     *
     * @var int
     */
    protected $payattr;

    /**
     * Признак предмета расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Lineattr
     *
     * @var int
     */
    protected $lineattr;

    /**
     * Дополнительные сведения о продукте.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item\Product|null
     */
    protected $product;

    /**
     * Данные агента.
     *
     * @var \Tmconsulting\Uniteller\Receipt\Item\Agent|null
     */
    protected $agent;

    /**
     * Планируемый статус товара, подлежащего обязательной маркировке средством идентификации (тег 2003, goodstatus).
     * Параметр обязателен для маркированного товара и может принимать одно из значений GoodStatus.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\GoodStatus
     *
     * @var int|null
     */
    protected $goodstatus;

    /**
     * Отраслевые реквизиты предмета расчёта.
     *
     * Каждый элемент массива содержит идентификатор федерального органа исполнительной власти, дату и номер
     * документа-основания, а также значение отраслевого реквизита. Значение передаётся в формате
     * param1=value1&param2=value2...; символ & внутри значения должен передаваться как &&.
     *
     * @var \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null
     */
    protected $industryProps;

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
     * @param int|null $goodstatus Планируемый статус товара, подлежащего обязательной маркировке средством идентификации (тег 2003, goodstatus).
     * @param \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null $industryProps Отраслевые реквизиты предмета расчёта.
     * @param array $rawData Исходные данные, т.е. ассоциативный массив, который получился из json.
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @throws \ReflectionException
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
        ?QtyPart $qtypart = null,
        ?int     $goodstatus = null,
        ?array   $industryProps = null,
        array    $rawData = []
    )
    {
        $this->name = $name;
        $this->price = (string)$price;
        $this->setQty($qty);
        $this->qtypart = $qtypart;
        $this->unit = $unit;
        $this->sum = (string)$sum;
        $this->vat = $vat;
        $this->setPayattr($payattr);
        $this->setLineattr($lineattr);
        $this->product = $product;
        $this->agent = $agent;
        $this->goodstatus = $goodstatus;
        $this->setIndustryProps($industryProps);
        $this->setRawData($rawData);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [
            'name'     => $this->name,
            'price'    => $this->price,
            'qty'      => $this->qty,
            'unit'     => $this->unit,
            'sum'      => $this->sum,
            'vat'      => $this->vat,
            'payattr'  => $this->payattr,
            'lineattr' => $this->lineattr,
        ];
        if ($this->qtypart !== null) {
            $arr['qtypart'] = $this->qtypart;
        }
        if ($this->product !== null) {
            $arr['product'] = $this->product;
        }
        if ($this->agent !== null) {
            $arr['agent'] = $this->agent;
        }
        if ($this->goodstatus !== null) {
            $arr['goodstatus'] = $this->goodstatus;
        }
        if ($this->industryProps !== null) {
            $arr['industryProps'] = $this->industryProps;
        }
        return $arr;
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
     * Возвращает наименование позиции.
     *
     * Максимальная длина — 128 символов.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает цену за единицу измерения.
     *
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * Возвращает количество товара.
     *
     * Значение не может быть равно нулю.
     *
     * @return int
     */
    public function getQty(): int
    {
        return $this->qty;
    }

    /**
     * Возвращает дробное количество маркированного товара.
     *
     * Параметр используется только для товаров с маркировкой.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Item\QtyPart|null
     */
    public function getQtyPart(): ?QtyPart
    {
        return $this->qtypart;
    }

    /**
     * Возвращает код меры количества предмета расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Unit
     *
     * @return int
     */
    public function getUnit(): int
    {
        return $this->unit;
    }

    /**
     * Возвращает сумму по позиции.
     *
     * @return string
     */
    public function getSum(): string
    {
        return $this->sum;
    }

    /**
     * Возвращает код значения ставки НДС.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Vat
     *
     * @return int
     */
    public function getVat(): int
    {
        return $this->vat;
    }

    /**
     * Возвращает признак способа расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Payattr
     *
     * @return int
     */
    public function getPayattr(): int
    {
        return $this->payattr;
    }

    /**
     * Возвращает признак предмета расчета.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\Lineattr
     *
     * @return int
     */
    public function getLineattr(): int
    {
        return $this->lineattr;
    }

    /**
     * Возвращает дополнительные сведения о продукте.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Item\Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * Возвращает данные агента.
     *
     * @return \Tmconsulting\Uniteller\Receipt\Item\Agent|null
     */
    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    /**
     * Планируемый статус товара, подлежащего обязательной маркировке средством идентификации (тег 2003, goodstatus).
     * Параметр обязателен для маркированного товара и может принимать одно из значений GoodStatus.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\GoodStatus
     *
     * @return int|null
     */
    public function getGoodstatus(): ?int
    {
        return $this->goodstatus;
    }

    /**
     * @param array|null $industryProps Отраслевые реквизиты предмета расчёта.
     */
    public function setIndustryProps(?array $industryProps)
    {
        $this->industryProps = !empty($industryProps) ? $industryProps : null;
    }

    /**
     * Возвращает отраслевые реквизиты предмета расчёта.
     *
     * @return \Tmconsulting\Uniteller\Receipt\IndustryProps[]|null
     */
    public function getIndustryProps(): ?array
    {
        return $this->industryProps;
    }
}
