<?php

namespace Tmconsulting\Uniteller\Support;


/**
 * Устанавливает политику логирования SDK.
 *
 * Политика управляет маскированием известных SDK полей персональных данных
 * (телефона, email, ФИО, адреса и покупателя) перед передачей записей логгеру.
 * По умолчанию маскирование включено. Чтобы отключить его, передайте политику,
 * настроенную вызовом LoggingPolicy::maskPersonalData(false).
 *
 * Чеки скрываются целиком при включённом маскировании персональных данных иначе сохраняются как есть.
 * Пароль и его промежуточные хеши скрываются независимо от настройки персональных данных.
 */
final class LoggingPolicy
{
    /**
     * @var bool Маскировать ли персональные данные в логах.
     */
    private $maskPersonalData;

    /**
     * @param bool $maskPersonalData Маскировать ли персональные данные в логах.
     */
    public function __construct(bool $maskPersonalData = true)
    {
        $this->maskPersonalData = $maskPersonalData;
    }

    /**
     * @param bool $maskPersonalData Маскировать ли персональные данные в логах.
     *
     * @return $this
     */
    public function maskPersonalData(bool $maskPersonalData = true): self
    {
        $this->maskPersonalData = $maskPersonalData;

        return $this;
    }

    /**
     * @return bool Маскируются ли персональные данные в логах.
     */
    public function shouldMaskPersonalData(): bool
    {
        return $this->maskPersonalData;
    }
}
