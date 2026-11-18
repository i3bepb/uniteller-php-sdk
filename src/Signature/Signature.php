<?php

namespace Tmconsulting\Uniteller\Signature;

use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Support\LoggingAwareInterface;
use Tmconsulting\Uniteller\Support\LoggingAwareTrait;
use Tmconsulting\Uniteller\Dependency\DebugAwareInterface;
use Tmconsulting\Uniteller\Dependency\DebugAwareTrait;
use Tmconsulting\Uniteller\Support\LogSanitizer;

/**
 * Class Signature
 */
class Signature implements LoggingAwareInterface, DebugAwareInterface
{
    use LoggingAwareTrait {
        setLogger as private setSanitizingLogger;
    }
    use DebugAwareTrait;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * Исходный логгер для полной диагностики расчёта подписи при включённом debug.
     *
     * @var LoggerInterface|null
     */
    private $debugLogger;

    /**
     * Устанавливает логгер для проверки и расчёта подписи.
     *
     * Для проверки подписи применяется общая политика маскирования.
     * Для отладки расчёта сохраняется исходный логгер: параметры и хеши
     * передаются ему без маскирования, только при включённом debug.
     *
     * @param LoggerInterface $logger Логгер приложения.
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->setSanitizingLogger($logger);
        $this->debugLogger = $logger;
    }

    /**
     * Проверка сигнатуры.
     *
     * @param string $signature
     *
     * @return bool
     */
    public function verify(string $signature): bool
    {
        $expected = strtoupper(md5(implode('', $this->parameters)));
        $valid = $expected === $signature;

        if ($this->debug && $this->logger !== null) {
            $this->logger->debug('uniteller.signature.verification_completed', [
                'uniteller' => [
                    'algorithm'          => 'md5',
                    'parameters'         => LogSanitizer::sanitizeSignatureParameters($this->parameters, $this->getLoggingPolicy()),
                    'expected_signature' => $expected,
                    'received_signature' => $signature,
                    'valid'              => $valid,
                ]
            ]);
        }

        return $valid;
    }

    /**
     * @param array $parameters Массив с полями из которых нужно сделать signature.
     *
     * @return static
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Создает signature на основе хеш-функции md5 и поля разделены '&'
     *
     * @return string
     */
    public function createMd5(): string
    {
        $string = implode('&', array_map(static function ($item) {
            return md5($item ?? '');
        }, $this->parameters));

        $this->debugSignatureCalculation($this->parameters, $string);

        return strtoupper(md5($string));
    }

    /**
     * Создает signature на основе хеш-функции md5 и поля идут друг за другом без разделителя.
     *
     * @return string
     */
    public function createMd5WithoutDelimiter(): string
    {
        $string = implode('', array_map(static function ($item) {
            return md5($item ?? '');
        }, $this->parameters));

        return strtoupper(md5($string));
    }

    /**
     * Создает signature на основе хеш-функции sha256 и поля разделены '&'.
     *
     * @return string
     */
    public function createSha256(): string
    {
        $str = implode('&', array_map(static function ($item) {
            return hash('sha256', $item);
        }, $this->parameters));

        $this->debugSignatureCalculation($this->parameters, $str, 'sha256');

        return strtoupper(hash('sha256', $str));
    }

    /**
     * Отладка создания сигнатуры без маскирования параметров и их хешей.
     *
     * Запись выполняется только при включённом debug и установленном логгере.
     * Исходные значения, включая пароль и чек, передаются логгеру приложения без маскирования.
     *
     * @param array $parameters Параметры участвующие в сигнатуре.
     * @param string $hash Промежуточное значение хэша для отладки.
     * @param string $hashLabel Какой метод расчета сигнатуры использовался.
     */
    private function debugSignatureCalculation(array $parameters, string $hash, string $hashLabel = 'md5')
    {
        if ($this->debug && $this->debugLogger !== null) {
            $hashes = $parameters ? array_combine(array_keys($parameters), explode('&', $hash)) : [];
            $this->debugLogger->debug('uniteller.signature.calculated', [
                'uniteller' => [
                    'algorithm'  => $hashLabel,
                    'parameters' => $parameters,
                    'hashes'     => $hashes,
                    'signature'  => strtoupper(hash($hashLabel, $hash)),
                ]
            ]);
        }
    }
}
