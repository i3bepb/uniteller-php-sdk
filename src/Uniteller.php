<?php

namespace Tmconsulting\Uniteller;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Callback\Callback;
use Tmconsulting\Uniteller\Cancel\CancelBuilder;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentType;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;

/**
 * Фасад для работы с Uniteller.
 *
 * Является основной точкой входа и предоставляет методы для выполнения различных операций:
 * - инициализация платежей (обычных, с преавторизацией, с фискализацией, СБП);
 * - подтверждение двухстадийных платежей;
 * - отмена (возврат) платежей;
 * - другие операции.
 */
class Uniteller
{
    /**
     * Внутренний контейнер зависимостей.
     *
     * @var \Tmconsulting\Uniteller\Dependency\Container
     */
    protected $container;

    /**
     * @param bool $debug Режим отладки.
     *
     * @throws \Tmconsulting\Uniteller\Dependency\InvalidServiceDefinitionException
     */
    public function __construct(bool $debug = false)
    {
        $this->container = new Container([], $debug);
    }

    /**
     * Включает или отключает режим отладки.
     *
     * @param bool $debug
     *
     * @return self
     */
    public function withDebug(bool $debug = true): self
    {
        $this->container->setDebug($debug);

        return $this;
    }

    /**
     * Устанавливает сервис в контейнер.
     *
     * @param string $id Идентификатор сервиса.
     * @param object|string $definition Реализация сервиса.
     *
     * @return $this
     *
     * @throws \Tmconsulting\Uniteller\Dependency\InvalidServiceDefinitionException
     */
    public function set(string $id, $definition): self
    {
        $this->container->set($id, $definition);

        return $this;
    }

    /**
     * Устанавливает HTTP-транспорт для выполнения запросов к API Uniteller.
     *
     * Необходим для сценариев, требующих обращения к серверу Uniteller
     * (например: получение результатов операций, подтверждение, отмена).
     *
     * В качестве зависимостей используются PSR-интерфейсы:
     * - ClientInterface — HTTP-клиент (PSR-18);
     * - RequestFactoryInterface — фабрика HTTP-запросов (PSR-17);
     * - StreamFactoryInterface — фабрика потоков (PSR-17).
     *
     * @param \Psr\Http\Client\ClientInterface $client HTTP-клиент.
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory Фабрика HTTP-запросов.
     * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory Фабрика потоков.
     *
     * @return self
     */
    public function withHttpTransport(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): self {
        $this->set(ClientInterface::class, $client);
        $this->set(RequestFactoryInterface::class, $requestFactory);
        $this->set(StreamFactoryInterface::class, $streamFactory);

        return $this;
    }

    /**
     * Устанавливает логгер.
     *
     * По умолчанию используется NullLogger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return self
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->set(LoggerInterface::class, $logger);

        return $this;
    }

    /**
     * Инициализация обычного платежа (одностадийная оплата).
     *
     * Денежные средства списываются с карты Плательщика сразу.
     * Фискализация (печать чека) не выполняется.
     *
     * Используется для базового сценария оплаты без применения требований 54-ФЗ.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function payment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::PAYMENT);

        return $builder;
    }

    /**
     * Инициализация платежа с преавторизацией (двухстадийная оплата).
     *
     * На первом этапе происходит блокировка денежных средств на счёте Плательщика. Списание выполняется позже
     * отдельным запросом подтверждения.
     * Фискализация (печать чеков) не выполняется.
     *
     * Используется, когда необходимо подтвердить операцию после проверки или выполнения услуги.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function preAuthPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::PAYMENT)
            ->setPreAuth(true);

        return $builder;
    }

    /**
     * Инициализация платежа с фискализацией (одностадийная оплата с чеком).
     *
     * Денежные средства списываются сразу после успешной авторизации.
     * Одновременно формируется и передаётся фискальный чек (тип "Приход") через параметр Receipt.
     *
     * Используется для соответствия требованиям 54-ФЗ при приёме оплаты.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function fiscalPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->requireReceipt()
            ->setEndpoint(ApiEndpoints::FISCAL_PAYMENT);

        return $builder;
    }

    /**
     * Инициализация платежа с преавторизацией и фискализацией (без чека аванса).
     *
     * На первом этапе выполняется блокировка денежных средств на счёте Плательщика.
     * Фискальный чек на этом этапе не формируется.
     *
     * Для завершения операции необходимо выполнить подтверждение платежа, в котором передаётся фискальный чек.
     * В этот момент формируется чек (тип "Приход").
     *
     * Используется для двухстадийных платежей, когда чек требуется только в момент окончательного списания средств.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function fiscalPreAuthPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::FISCAL_PREAUTH_PAYMENT)
            ->setPreAuth(true);

        return $builder;
    }

    /**
     * Инициализация платежа с преавторизацией и фискализацией (с чеком аванса).
     *
     * На первом этапе выполняется блокировка денежных средств на счёте Плательщика, при этом автоматически формируется
     * фискальный чек (тип "Аванс"). Передача параметра Receipt в этом запросе не требуется.
     *
     * Для завершения операции необходимо выполнить подтверждение платежа, в котором передаётся фискальный чек
     * для зачёта аванса (тип "Приход" или "Приход с зачётом аванса").
     *
     * Используется для двухстадийных платежей, когда требуется чек аванса (в соответствии с требованиями 54-ФЗ).
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function fiscalPreAuthPaymentWithAdvanceReceipt(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::FISCAL_PREAUTH_PAYMENT_WITH_ADVANCE_RECEIPT)
            ->setPreAuth(true);

        return $builder;
    }

    /**
     * Сценарий родительского платежа для рекуррентных списаний.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function recurrentStartPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::PAYMENT)
            ->setIsRecurrentStart(true);

        return $builder;
    }

    /**
     * Сценарий родительского платежа для рекуррентных списаний с фискализацией.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function recurrentStartFiscalPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::FISCAL_PAYMENT)
            ->setIsRecurrentStart(true);

        return $builder;
    }

    /**
     * Рекуррентное списание.
     *
     * @return \Tmconsulting\Uniteller\Recurrent\RecurrentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function recurrentContinuePayment(): RecurrentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Recurrent\RecurrentBuilder $builder */
        $builder = $this->container->get(RecurrentBuilder::class);

        return $builder;
    }

    /**
     * Инициализация платежа через Систему быстрых платежей (СБП).
     *
     * Плательщику отображается сценарий оплаты через СБП:
     * QR-код, ссылка или страница выбора банка с переходом в банковское приложение.
     *
     * Списание денежных средств выполняется сразу после подтверждения оплаты Плательщиком.
     * Фискализация (печать чеков) не выполняется.
     *
     * Используется для приёма оплаты через СБП без формирования фискального чека.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function sbpPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::PAYMENT)
            ->setPaymentType(PaymentType::SBP);

        return $builder;
    }

    /**
     * Инициализация платежа через Систему быстрых платежей (СБП) с фискализацией.
     *
     * Плательщику отображается сценарий оплаты через СБП: QR-код, ссылка или страница выбора банка.
     *
     * После успешной оплаты выполняется списание денежных средств и формируется фискальный чек (тип "Приход")
     * на основе переданных данных (Receipt).
     *
     * Используется для приёма оплаты через СБП с соблюдением требований 54-ФЗ.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function sbpFiscalPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->requireReceipt()
            ->setEndpoint(ApiEndpoints::FISCAL_PAYMENT)
            ->setPaymentType(PaymentType::SBP);

        return $builder;
    }

    /**
     * Инициализация платежа через Систему быстрых платежей (СБП) для B2B.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function sbpB2bPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->setEndpoint(ApiEndpoints::PAYMENT)
            ->setPaymentType(PaymentType::SBP_B2B);

        return $builder;
    }

    /**
     * Инициализация платежа через Систему быстрых платежей (СБП) с фискализацией для B2B.
     *
     * @return \Tmconsulting\Uniteller\Payment\PaymentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function sbpFiscalB2bPayment(): PaymentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder */
        $builder = $this->container->get(PaymentBuilder::class)
            ->requireReceipt()
            ->setEndpoint(ApiEndpoints::FISCAL_PAYMENT)
            ->setPaymentType(PaymentType::SBP_B2B);

        return $builder;
    }

    /**
     * Отмена платежа с чеком.
     *
     * @return \Tmconsulting\Uniteller\Cancel\CancelBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function cancel(): CancelBuilder
    {
        /** @var \Tmconsulting\Uniteller\Cancel\CancelBuilder $builder */
        $builder = $this->container->get(CancelBuilder::class);

        return $builder;
    }

    /**
     * Сценарий подтверждения преавторизации.
     *
     * @return \Tmconsulting\Uniteller\Confirm\ConfirmBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function confirm(): ConfirmBuilder
    {
        /** @var \Tmconsulting\Uniteller\Confirm\ConfirmBuilder $builder */
        $builder = $this->container->get(ConfirmBuilder::class);

        return $builder;
    }

    /**
     * Сценарий подтверждения преавторизации с чеком.
     *
     * @return \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function fiscalConfirm(): FiscalConfirmBuilder
    {
        /** @var \Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder $builder */
        $builder = $this->container->get(FiscalConfirmBuilder::class);

        return $builder;
    }

    /**
     * Сценарий запроса результатов.
     *
     * @return \Tmconsulting\Uniteller\Results\ResultsBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function results(): ResultsBuilder
    {
        /** @var \Tmconsulting\Uniteller\Results\ResultsBuilder $builder */
        $builder = $this->container->get(ResultsBuilder::class);

        return $builder;
    }

    /**
     * Сценарий запроса результатов с чеком.
     *
     * @return \Tmconsulting\Uniteller\Results\FiscalResultsBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function fiscalResults(): FiscalResultsBuilder
    {
        /** @var \Tmconsulting\Uniteller\Results\FiscalResultsBuilder $builder */
        $builder = $this->container->get(FiscalResultsBuilder::class);

        return $builder;
    }

    /**
     * Сценарий рекуррентного платежа.
     *
     * @return \Tmconsulting\Uniteller\Recurrent\RecurrentBuilder
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function recurrent(): RecurrentBuilder
    {
        /** @var \Tmconsulting\Uniteller\Recurrent\RecurrentBuilder $builder */
        $builder = $this->container->get(RecurrentBuilder::class);

        return $builder;
    }

    /**
     * Обработчик callback-запроса.
     *
     * @return \Tmconsulting\Uniteller\Callback\Callback
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function callback(): Callback
    {
        /** @var \Tmconsulting\Uniteller\Callback\Callback $callback */
        $callback = $this->container->get(Callback::class);

        return $callback;
    }
}
