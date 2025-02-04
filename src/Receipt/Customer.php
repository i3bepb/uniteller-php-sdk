<?php

namespace Tmconsulting\Uniteller\Receipt;

/**
 * Контакты плательщика для отправки текста фискального чека.
 */
class Customer implements \JsonSerializable
{
    /**
     * Номер телефона плательщика.
     *
     * @var string|null
     */
    private $phone;

    /**
     * Email плательщика.
     *
     * @var string|null
     */
    private $email;

    /**
     * Идентификатор плательщика, присвоенный мерчантом.
     *
     * @var string|null
     */
    private $id;

    /**
     * Название плательщика.
     * Максимально 243 символов включительно.
     *
     * @var string|null
     */
    private $name;

    /**
     * ИНН плательщика.
     * 10-12 цифр.
     *
     * @var int|null
     */
    private $inn;

    /**
     * Дата рождения плательщика.
     * Формат ДД.ММ.ГГГГ
     *
     * @var string|null
     */
    private $birthday;

    /**
     * Гражданство. Три цифры код страны.
     * Например, 643 для России, 840 для США.
     *
     * @var string|null
     */
    private $citizenship;

    /**
     * Код вида документа, удостоверяющего личность.
     * Например: 21 – Паспорт гражданина РФ.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\DocCode
     *
     * @var string|null
     */
    private $doccode;

    /**
     * Данные документа, удостоверяющего личность.
     * Строка до 64 символов.
     *
     * @var string|null
     */
    private $docdata;

    /**
     * Адрес плательщика.
     * Строка до 256 символов.
     *
     * @var string|null
     */
    private $address;

    /**
     * @param string|null $phone Номер телефона плательщика.
     * @param string|null $email Email плательщика.
     * @param string|null $id Идентификатор плательщика, присвоенный мерчантом.
     * @param string|null $name Название плательщика. Максимально 243 символов включительно.
     * @param int|string|null $inn ИНН плательщика. 10-12 цифр.
     * @param string|null $birthday Дата рождения плательщика. Формат ДД.ММ.ГГГГ
     * @param string|null $citizenship Гражданство. Три цифры. Смотри стандарт ISO 3166-1 numeric, например, 643 для России.
     * @param string|null $doccode Код вида документа, удостоверяющего личность. See \Tmconsulting\Uniteller\Receipt\Enum\DocCode
     * @param string|null $docdata Данные документа, удостоверяющего личность.
     * @param string|null $address Адрес плательщика.
     */
    public function __construct(
        ?string $phone = null,
        ?string $email = null,
        ?string $id = null,
        ?string $name = null,
        $inn = null,
        ?string $birthday = null,
        ?string $citizenship = null,
        ?string $doccode = null,
        ?string $docdata = null,
        ?string $address = null
    )
    {
        $this->phone = $phone;
        $this->email = $email;
        $this->id = $id;
        $this->name = $name;
        $this->setInn($inn);
        $this->birthday = $birthday;
        $this->citizenship = $citizenship;
        $this->doccode = $doccode;
        $this->docdata = $docdata;
        $this->address = $address;
    }

    /**
     * @param int|string|null $inn
     */
    protected function setInn($inn)
    {
        if (!empty($inn)) {
            $this->inn = (int)$inn;
        } else {
            $this->inn = null;
        }
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [];
        if (!empty($this->phone)) {
            $arr['phone'] = $this->phone;
        }
        if (!empty($this->email)) {
            $arr['email'] = $this->email;
        }
        if (!empty($this->id)) {
            $arr['id'] = $this->id;
        }
        if (!empty($this->name)) {
            $arr['name'] = $this->name;
        }
        if (!empty($this->inn)) {
            $arr['inn'] = $this->inn;
        }
        if (!empty($this->birthday)) {
            $arr['birthday'] = $this->birthday;
        }
        if (!empty($this->citizenship)) {
            $arr['citizenship'] = $this->citizenship;
        }
        if (!empty($this->doccode)) {
            $arr['doccode'] = (int)$this->doccode;
        }
        if (!empty($this->docdata)) {
            $arr['docdata'] = $this->docdata;
        }
        if (!empty($this->address)) {
            $arr['address'] = $this->address;
        }
        return $arr;
    }
}
