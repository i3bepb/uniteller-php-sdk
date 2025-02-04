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
    protected $phone;

    /**
     * Email плательщика.
     *
     * @var string|null
     */
    protected $email;

    /**
     * Идентификатор плательщика, присвоенный мерчантом.
     *
     * @var string|null
     */
    protected $id;

    /**
     * Имя/наименование плательщика.
     * Максимально 243 символов включительно.
     *
     * @var string|null
     */
    protected $name;

    /**
     * ИНН плательщика. 10 или 12 цифр.
     *
     * @var string|null
     */
    protected $inn;

    /**
     * Дата рождения плательщика.
     * Формат ДД.ММ.ГГГГ
     *
     * @var string|null
     */
    protected $birthday;

    /**
     * Гражданство по ОКСМ — Общероссийскому классификатору стран мира. Три цифры код страны.
     * Например, 643 для России, 840 для США.
     *
     * @var string|null
     */
    protected $citizenship;

    /**
     * Код вида документа, удостоверяющего личность.
     * Например: 21 – Паспорт гражданина РФ.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\DocCode
     *
     * @var string|null
     */
    protected $doccode;

    /**
     * Данные документа, удостоверяющего личность.
     * Строка до 64 символов.
     *
     * @var string|null
     */
    protected $docdata;

    /**
     * Адрес плательщика.
     * Строка до 256 символов.
     *
     * @var string|null
     */
    protected $address;

    /**
     * @param string|null $phone Номер телефона плательщика.
     * @param string|null $email Email плательщика.
     * @param string|null $id Идентификатор плательщика, присвоенный мерчантом.
     * @param string|null $name Имя/наименование плательщика. Максимально 243 символов включительно.
     * @param int|string|null $inn ИНН плательщика. 10 или 12 цифр.
     * @param string|null $birthday Дата рождения плательщика. Формат ДД.ММ.ГГГГ
     * @param string|null $citizenship Гражданство. Три цифры. Смотри стандарт ISO 3166-1 numeric, например, 643 для России.
     * @param string|int|null $doccode Код вида документа, удостоверяющего личность. Смотри \Tmconsulting\Uniteller\Receipt\Enum\DocCode.
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
        $doccode = null,
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
        $this->setDocCode($doccode);
        $this->docdata = $docdata;
        $this->address = $address;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $arr = [];
        if ($this->phone !== null) {
            $arr['phone'] = $this->phone;
        }
        if ($this->email !== null) {
            $arr['email'] = $this->email;
        }
        if ($this->id !== null) {
            $arr['id'] = $this->id;
        }
        if ($this->name !== null) {
            $arr['name'] = $this->name;
        }
        if ($this->inn !== null) {
            $arr['inn'] = $this->inn;
        }
        if ($this->birthday !== null) {
            $arr['birthday'] = $this->birthday;
        }
        if ($this->citizenship !== null) {
            $arr['citizenship'] = $this->citizenship;
        }
        if ($this->doccode !== null) {
            $arr['doccode'] = $this->doccode;
        }
        if ($this->docdata !== null) {
            $arr['docdata'] = $this->docdata;
        }
        if ($this->address !== null) {
            $arr['address'] = $this->address;
        }
        return $arr;
    }
    /**
     * Возвращает номер телефона плательщика.
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Возвращает email плательщика.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Возвращает идентификатор плательщика, присвоенный мерчантом.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Возвращает название плательщика.
     * Максимальная длина — 243 символа включительно.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param int|string|null $inn
     */
    protected function setInn($inn)
    {
        $this->inn = ($inn !== null && $inn !== '' ? (string) $inn : null);
    }

    /**
     * Возвращает ИНН плательщика.
     *
     * @return string|null
     */
    public function getInn(): ?string
    {
        return $this->inn;
    }

    /**
     * Возвращает дату рождения плательщика.
     * Формат: ДД.ММ.ГГГГ.
     *
     * @return string|null
     */
    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    /**
     * Возвращает код страны гражданства плательщика.
     *
     * Трёхзначный код страны по стандарту ISO 3166-1 numeric.
     * Например: 643 — Россия, 840 — США.
     *
     * @return string|null
     */
    public function getCitizenship(): ?string
    {
        return $this->citizenship;
    }

    /**
     * @param string|int|null $docCode Код вида документа, удостоверяющего личность.
     */
    protected function setDocCode($docCode)
    {
        $this->doccode = ($docCode !== null && $docCode !== '' ? (string) $docCode : null);
    }

    /**
     * Возвращает код вида документа, удостоверяющего личность.
     * Например: 21 — паспорт гражданина РФ.
     *
     * @see \Tmconsulting\Uniteller\Receipt\Enum\DocCode
     *
     * @return string|null
     */
    public function getDocCode(): ?string
    {
        return $this->doccode;
    }

    /**
     * Возвращает данные документа, удостоверяющего личность.
     * Максимальная длина — 64 символа.
     *
     * @return string|null
     */
    public function getDocData(): ?string
    {
        return $this->docdata;
    }

    /**
     * Возвращает адрес плательщика.
     * Максимальная длина — 256 символов.
     *
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }
}
