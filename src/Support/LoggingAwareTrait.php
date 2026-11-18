<?php

namespace Tmconsulting\Uniteller\Support;

use Psr\Log\LoggerInterface;

/**
 * Реализует настройку логгера и политики логирования для компонентов SDK.
 *
 * Переданный логгер оборачивается в SanitizingLogger, который маскирует данные
 * перед записью согласно текущей политике. Если политика не задана явно,
 * она создаётся при первом обращении к getLoggingPolicy().
 *
 * @see \Tmconsulting\Uniteller\Support\LoggingAwareInterface
 */
trait LoggingAwareTrait
{
    /**
     * Логгер компонента, обёрнутый в SanitizingLogger при вызове setLogger().
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Текущая политика маскирования данных; null до её установки или создания.
     *
     * @var LoggingPolicy|null
     */
    private $loggingPolicy;

    /**
     * Устанавливает логгер компонента с маскированием данных в записях SDK.
     *
     * Оборачивает переданный логгер в SanitizingLogger с текущей политикой.
     * Если политика ещё не установлена, используется политика по умолчанию.
     *
     * @param \Psr\Log\LoggerInterface $logger Логгер для записи обработанных сообщений и контекста.
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = new SanitizingLogger($logger, $this->getLoggingPolicy());
    }

    /**
     * Устанавливает политику маскирования данных в логах компонента.
     *
     * Сохраняет политику для последующих вызовов setLogger() и обновляет её
     * в уже установленном SanitizingLogger. Политику можно задать как до,
     * так и после установки логгера.
     *
     * @param \Tmconsulting\Uniteller\Support\LoggingPolicy $policy Политика маскирования данных в логах SDK.
     *
     * @return void
     */
    public function setLoggingPolicy(LoggingPolicy $policy): void
    {
        $this->loggingPolicy = $policy;
        if ($this->logger instanceof SanitizingLogger) {
            $this->logger->setLoggingPolicy($policy);
        }
    }

    /**
     * Возвращает текущую политику маскирования данных в логах компонента.
     *
     * Если политика ещё не задана, создаёт и сохраняет LoggingPolicy
     * с настройками по умолчанию. Последующие вызовы возвращают тот же
     * экземпляр до его замены методом setLoggingPolicy().
     *
     * @return \Tmconsulting\Uniteller\Support\LoggingPolicy Текущая политика логирования.
     */
    protected function getLoggingPolicy(): LoggingPolicy
    {
        if ($this->loggingPolicy === null) {
            $this->loggingPolicy = new LoggingPolicy();
        }

        return $this->loggingPolicy;
    }
}
