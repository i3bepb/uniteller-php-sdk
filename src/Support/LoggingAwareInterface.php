<?php

namespace Tmconsulting\Uniteller\Support;

use Psr\Log\LoggerAwareInterface;

/**
 * Контракт компонентов, поддерживающих настройку логгера и политики логирования.
 * Расширяет PSR-3 LoggerAwareInterface: логгер задаётся методом setLogger(), а правила маскирования данных в его
 * записях — методом setLoggingPolicy().
 */
interface LoggingAwareInterface extends LoggerAwareInterface
{
    /**
     * Устанавливает политику маскирования данных в логах компонента.
     *
     * Политика определяет, нужно ли скрывать известные SDK поля персональных данных перед передачей записей логгеру.
     * Чеки скрываются целиком при включённом маскировании персональных данных; иначе сохраняются как есть.
     * Пароли и их промежуточные хеши скрываются независимо от этой настройки.
     *
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных в логах.
     *
     * @return void
     */
    public function setLoggingPolicy(LoggingPolicy $policy): void;
}
