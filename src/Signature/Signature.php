<?php

namespace Tmconsulting\Uniteller\Signature;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Dependency\DebugAwareInterface;
use Tmconsulting\Uniteller\Dependency\DebugAwareTrait;

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
        return strtoupper(md5(implode('', $this->parameters))) === $signature;
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
     * Отладка создания сигнатуры.
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
            $str = "Signature {$hashLabel}: " . PHP_EOL . print_r($parameters, true) . PHP_EOL
                . 'Calculation: ' . PHP_EOL;
            $arr1 = explode('&', $hash);
            $arr2 = [];
            $i = 0;
            foreach ($parameters as $k => $v) {
                $arr2["$hashLabel('$k')"] = $arr1[$i];
                ++$i;
            }
            $str .= print_r($arr2, true) . PHP_EOL;
            $str .= "strtoupper({$hashLabel}('$hash'))\n";
            $this->logger->debug($str);
        }
    }
}
