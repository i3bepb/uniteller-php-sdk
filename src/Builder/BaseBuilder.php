<?php

namespace Tmconsulting\Uniteller\Builder;

use Tmconsulting\Uniteller\Common\BaseUri;
use Tmconsulting\Uniteller\Common\EMoneyType;
use Tmconsulting\Uniteller\Common\MeanType;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;
use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;

abstract class BaseBuilder
{
    /**
     * @var string
     */
    protected $baseUri = BaseUri::NON_RECEIPTED;
    /**
     * Идентификатор точки продажи в системе Uniteller.
     * Доступен Мерчанту в Личном кабинете в пункте меню «Точки продажи», столбец Uniteller Point ID.
     *
     * @var string
     */
    protected $shopId;
    /**
     * Логин. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    protected $login;
    /**
     * Пароль. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    protected $password;
    /**
     * Платёжная система кредитной карты.
     * Может принимать значения: 0 — любая, 1 — VISA, 2 — MasterCard,
     * 3 — Diners Club, 4 — JCB, 5 — American Express.
     *
     * @see \Tmconsulting\Uniteller\Common\MeanType
     *
     * @var int
     */
    protected $meanType;
    /**
     * Тип электронной валюты.
     * 0 - Любая система электронных платежей
     * 1 - Яндекс.Деньги
     * 13 - Оплата наличными (Евросеть, Яндекс.Деньги и пр.)
     * 18 - QIWI Кошелек REST (по протоколу REST)
     * 29 - WebMoney WMR
     *
     * @see \Tmconsulting\Uniteller\Common\EMoneyType
     *
     * @var int
     */
    protected $eMoneyType;

    /**
     * @param string $uri Базовая часть url-а - протокол, доменное имя
     *
     * @return static
     */
    public function setBaseUri(string $uri)
    {
        $this->baseUri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @param string $shopId Идентификатор точки продажи в системе Uniteller.
     *
     * @return static
     */
    public function setShopId(string $shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Возвращает идентификатор точки продажи в системе Uniteller.
     *
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getShopId(): string
    {
        if (empty($this->shopId)) {
            throw new RequiredParameterException(NameFieldsUniteller::SHOP_ID);
        }

        return $this->shopId;
    }

    /**
     * @param string $login
     *
     * @return static
     */
    public function setLogin(string $login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getLogin(): string
    {
        if (empty($this->login)) {
            throw new RequiredParameterException(NameFieldsUniteller::LOGIN);
        }
        return $this->login;
    }

    /**
     * @param string $password Пароль
     *
     * @return static
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getPassword(): string
    {
        if (empty($this->password)) {
            throw new RequiredParameterException(NameFieldsUniteller::PASSWORD);
        }
        return $this->password;
    }

    /**
     * @param int $meanType
     *
     * @return static
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setMeanType(int $meanType)
    {
        $types = MeanType::toArray();
        if (!in_array($meanType, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::MEAN_TYPE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->meanType = $meanType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMeanType(): ?int
    {
        return $this->meanType;
    }

    /**
     * @param int $eMoneyType
     *
     * @return static
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function setEMoneyType(int $eMoneyType)
    {
        $types = EMoneyType::toArray();
        if (!in_array($eMoneyType, $types, true)) {
            throw new NotValidParameterException(
                'Not valid parameter ' . NameFieldsUniteller::E_MONEY_TYPE . ', must be one of the values: ' . implode(',', $types)
            );
        }
        $this->eMoneyType = $eMoneyType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEMoneyType(): ?int
    {
        return $this->eMoneyType;
    }

}
