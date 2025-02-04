<?php

namespace Tmconsulting\Uniteller\Confirm;

class ConfirmBuilder
{
    /**
     * Логин. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    protected $login;

    /**
     * @param string $login
     *
     * @return \Tmconsulting\Uniteller\Confirm\ConfirmBuilder
     */
    public function setLogin(string $login): ConfirmBuilder
    {
        $this->login = $login;

        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

}