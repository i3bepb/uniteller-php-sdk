<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

// Для дебага
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$shopId = 'your_shop_id';
$login = '12345';
$password = 'your_password';
$email = 'test@gmail.com';
$phone = '+78004005001';

/**
 * Класс для вывода лога прямо в браузер
 */
class ShowInBrowserLogger extends \Psr\Log\AbstractLogger implements \Psr\Log\LoggerInterface
{
    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        echo '<pre>';
        echo "{$level}: {$message}" . PHP_EOL;
        echo print_r($context, true) . PHP_EOL;
        echo '</pre>';
    }
}
