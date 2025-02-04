<?php

namespace Tmconsulting\Uniteller\Signature;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Dependency\DebugAwareInterface;
use Tmconsulting\Uniteller\Dependency\DebugAwareTrait;
use Tmconsulting\Uniteller\Support\LogSanitizer;

/**
 * Class Signature
 */
class Signature implements LoggerAwareInterface, DebugAwareInterface
{
    use LoggerAwareTrait;
    use DebugAwareTrait;

    /**
     * @var array
     */
    protected $parameters = [];

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
            $parameters = $this->parameters;
            if ($parameters !== []) {
                // В старом формате без имён полей пароль — последнее значение.
                end($parameters);
                $lastKey = key($parameters);
                if (is_int($lastKey)) {
                    $parameters[$lastKey] = '*****';
                }
            }
            $this->logger->debug('Signature verify md5', [
                'parameters' => LogSanitizer::sanitize($parameters),
                'expected_signature' => $expected,
                'received_signature' => $signature,
                'valid' => $valid,
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
     * Отладка создания сигнатуры: исходный пароль скрыт, хеши параметров выводятся полностью.
     *
     * Внимание: в лог могут попасть чувствительные данные, поэтому режим не следует включать в production-окружении!
     *
     * @param array $parameters Параметры участвующие в сигнатуре.
     * @param string $hash Промежуточное значение хэша для отладки.
     * @param string $hashLabel Какой метод расчета сигнатуры использовался.
     */
    private function debugSignatureCalculation(array $parameters, string $hash, string $hashLabel = 'md5')
    {
        if ($this->debug) {
            $hashes = $parameters ? array_combine(array_keys($parameters), explode('&', $hash)) : [];
            $this->logger->debug("Signature {$hashLabel}", [
                'parameters' => LogSanitizer::sanitize($parameters),
                'hashes' => $hashes,
                'signature' => strtoupper(hash($hashLabel, $hash)),
            ]);
        }
    }
}
