<?php

namespace Tmconsulting\Uniteller\Dependency;

use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tmconsulting\Uniteller\Builder\BuilderInterface;
use Tmconsulting\Uniteller\Callback\Callback;
use Tmconsulting\Uniteller\Cancel\CancelBuilder;
use Tmconsulting\Uniteller\Confirm\ConfirmBuilder;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Recurrent\RecurrentBuilder;
use Tmconsulting\Uniteller\Request\ParserCsv;
use Tmconsulting\Uniteller\Request\ParserInterface;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Results\FiscalResultsBuilder;
use Tmconsulting\Uniteller\Results\ResultsBuilder;
use Tmconsulting\Uniteller\Signature\Signature;

/**
 * Контейнер зависимостей библиотеки Uniteller.
 */
class Container implements ContainerInterface, DebugAwareInterface
{
    use DebugAwareTrait;

    /**
     * Привязки контейнера.
     * В качестве значения может быть:
     * - имя класса;
     * - готовый объект.
     *
     * @var array<string, object|string>
     */
    protected $bindings = [];

    /**
     * @param array<string, object|string> $bindings Привязки контейнера.
     * @param bool $debug Режим отладки.
     *
     * @throws \Tmconsulting\Uniteller\Dependency\InvalidServiceDefinitionException
     */
    public function __construct(array $bindings = [], bool $debug = false)
    {
        $this->debug = $debug;

        $defaults = [
            Callback::class                => Callback::class,
            CancelBuilder::class           => CancelBuilder::class,
            ConfirmBuilder::class          => ConfirmBuilder::class,
            LoggerInterface::class         => NullLogger::class,
            ParserInterface::class         => ParserCsv::class,
            ParserReceiptFromBase64::class => ParserReceiptFromBase64::class,
            PaymentBuilder::class          => PaymentBuilder::class,
            FiscalConfirmBuilder::class    => FiscalConfirmBuilder::class,
            RecurrentBuilder::class        => RecurrentBuilder::class,
            RequestManager::class          => RequestManager::class,
            ResultsBuilder::class          => ResultsBuilder::class,
            FiscalResultsBuilder::class    => FiscalResultsBuilder::class,
            Signature::class               => Signature::class,
        ];

        foreach (array_merge($defaults, $bindings) as $id => $definition) {
            $this->set($id, $definition);
        }
    }

    /**
     * Возвращает сервис по его идентификатору.
     *
     * @template T
     *
     * @param class-string|string<T> $id
     *
     * @return T
     *
     * @throws \Psr\Container\NotFoundExceptionInterface Если сервис не найден.
     * @throws \ReflectionException Если не удалось создать экземпляр класса через Reflection.
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException($id);
        }
        $definition = $this->bindings[$id];
        // Если в контейнер уже положен готовый объект, возвращаем его как есть.
        if (is_object($definition)) {
            return $definition;
        }
        $entry = $this->build($definition);
        // Общие сервисы кэшируются, для stateful-объектов всегда создаётся новый экземпляр.
        if ($this->isShared($id)) {
            $this->bindings[$id] = $entry;
        }
        return $entry;
    }

    /**
     * Проверяет, может ли контейнер вернуть сервис по указанному идентификатору.
     *
     * @param string $id Идентификатор сервиса.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Регистрирует привязку в контейнере.
     *
     * В качестве значения может быть:
     * - имя класса;
     * - готовый объект.
     *
     * @param class-string|string $id Идентификатор сервиса.
     * @param object|class-string $definition Реализация сервиса.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Dependency\InvalidServiceDefinitionException
     */
    public function set(string $id, $definition): void
    {
        if (!is_string($definition) && !is_object($definition)) {
            throw new InvalidServiceDefinitionException(
                $id,
                'Определение сервиса должно быть именем класса или готовым объектом.'
            );
        }
        if (is_string($definition) && !class_exists($definition)) {
            throw new InvalidServiceDefinitionException(
                $id,
                'Класс "' . $definition . '" не существует.'
            );
        }
        if ($this->isPrototype($id) && is_object($definition)) {
            throw new InvalidServiceDefinitionException(
                $id,
                'Нельзя регистрировать готовый объект для сервиса, который должен создаваться заново при каждом запросе. Передайте имя класса.'
            );
        }
        $this->bindings[$id] = $definition;
    }

    /**
     * Создаёт экземпляр класса и внедряет в него необходимые зависимости.
     *
     * @param class-string|string $class Имя класса.
     *
     * @return object
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    protected function build(string $class): object
    {
        $interfaces = class_implements($class);
        if ($interfaces === false) {
            $interfaces = [];
        }
        $args = [];
        if (isset($interfaces[BuilderInterface::class]) || $class === Callback::class) {
            $args[] = $this->get(Signature::class);
        }
        if ($class === RequestManager::class) {
            $args[] = $this->get(RequestFactoryInterface::class);
            $args[] = $this->get(StreamFactoryInterface::class);
            $args[] = $this->get(ClientInterface::class);
            $args[] = $this->get(ParserInterface::class);
        }
        if (isset($interfaces[ParserInterface::class])) {
            $args[] = $this->get(ParserReceiptFromBase64::class);
        }
        $reflection = new \ReflectionClass($class);
        $entry = $reflection->newInstanceArgs($args);
        if ($entry instanceof LoggerAwareInterface) {
            $entry->setLogger($this->get(LoggerInterface::class));
        }
        if ($entry instanceof DebugAwareInterface) {
            $entry->setDebug($this->debug);
        }
        if ($entry instanceof ContainerAwareInterface) {
            $entry->setContainer($this);
        }
        return $entry;
    }

    /**
     * Определяет, должен ли сервис кэшироваться в контейнере.
     *
     * @param string $id Идентификатор сервиса.
     *
     * @return bool
     */
    protected function isShared(string $id): bool
    {
        return !$this->isPrototype($id);
    }

    /**
     * Определяет, должен ли контейнер создавать новый экземпляр сервиса при каждом запросе.
     *
     * @param string $id Идентификатор сервиса.
     *
     * @return bool
     */
    protected function isPrototype(string $id): bool
    {
        return in_array($id, [
            Callback::class,
            CancelBuilder::class,
            ConfirmBuilder::class,
            PaymentBuilder::class,
            FiscalConfirmBuilder::class,
            RecurrentBuilder::class,
            ResultsBuilder::class,
            FiscalResultsBuilder::class,
        ], true);
    }
}
