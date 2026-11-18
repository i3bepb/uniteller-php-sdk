<?php

namespace Tmconsulting\Uniteller\Support;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Декоратор PSR-3 логгера, маскирующий данные в контексте записей.
 *
 * Перед передачей записи исходному логгеру обрабатывает контекст через LogSanitizer с текущей политикой логирования.
 * Сообщение и уровень передаются без изменений.
 */
final class SanitizingLogger extends AbstractLogger
{
    /**
     * Исходный логгер, которому передаются записи после маскирования контекста.
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Текущая политика маскирования данных в контексте записей.
     *
     * @var \Tmconsulting\Uniteller\Support\LoggingPolicy
     */
    private $policy;

    /**
     * Создаёт обёртку над логгером с заданной политикой маскирования данных.
     *
     * @param \Psr\Log\LoggerInterface $logger Исходный логгер для записи сообщений с обработанным контекстом.
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных в логах SDK.
     */
    public function __construct(LoggerInterface $logger, LoggingPolicy $policy)
    {
        $this->logger = $logger;
        $this->policy = $policy;
    }

    /**
     * Заменяет политику маскирования данных для последующих записей.
     *
     * Исходный логгер сохраняется; новая политика применяется при следующем
     * вызове log(), в том числе через методы стандартных уровней логирования.
     *
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Новая политика маскирования данных.
     *
     * @return void
     */
    public function setLoggingPolicy(LoggingPolicy $policy): void
    {
        $this->policy = $policy;
    }

    /**
     * Маскирует данные в контексте и передаёт запись исходному логгеру.
     *
     * Контекст обрабатывается через LogSanitizer согласно текущей политике.
     * Само сообщение не очищается: чувствительные данные следует передавать
     * в контексте, в полях, распознаваемых LogSanitizer.
     *
     * Для стандартных уровней PSR-3 вызывается соответствующий метод исходного
     * логгера (например, info() или error()). Остальные значения уровня
     * передаются в его метод log() для дальнейшей обработки и проверки.
     *
     * @param mixed $level Уровень логирования.
     * @param string|object $message Сообщение: строка или объект с методом __toString().
     * @param array $context Контекст записи, подлежащий маскированию.
     *
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $context = LogSanitizer::sanitize($context, $this->policy);
        if (in_array($level, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'], true)) {
            $this->logger->{$level}($message, $context);
        } else {
            $this->logger->log($level, $message, $context);
        }
    }
}
