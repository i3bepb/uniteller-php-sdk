<?php

namespace Tmconsulting\Uniteller\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tmconsulting\Uniteller\Confirm\FiscalConfirmBuilder;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

/**
 * Общие средства проверки разбора ответов через билдеры и контейнер зависимостей.
 */
abstract class ResponseTestCase extends TestCase
{
    /**
     * Создаёт контейнер с HTTP-клиентом, возвращающим подготовленные ответы по порядку.
     *
     * @param \Psr\Http\Message\ResponseInterface[] $responses Ответы для последовательных запросов.
     * @param callable|null $inspect Проверка исходящего запроса перед выдачей очередного ответа.
     *
     * @return Container Контейнер с тестовым HTTP-клиентом и настоящими парсерами.
     */
    protected function responseContainer(array $responses, callable $inspect = null): Container
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(count($responses)))->method('sendRequest')->willReturnCallback(
            function ($request) use (&$responses, $inspect) {
                if ($inspect !== null) {
                    $inspect($request);
                }
                return array_shift($responses);
            }
        );
        return new Container([
            ClientInterface::class => $client,
            RequestFactoryInterface::class => new HttpFactory(),
            StreamFactoryInterface::class => new HttpFactory(),
        ]);
    }

    /**
     * @return array Данные фискальных чеков из сохранённого JSON-ответа отмены.
     */
    protected function receiptData(): array
    {
        // Тестовый пример содержит массив фискальных чеков в структуре приложения 2.
        $data = json_decode($this->getStubContents('responseCancelWithReceipt', 'json'), true);
        return json_decode(base64_decode($data['Receipt']), true);
    }

    /**
     * @param Container $container Контейнер с подготовленной последовательностью HTTP-ответов.
     *
     * @return FiscalConfirmBuilder Билдер с обязательными тестовыми параметрами и чеком.
     */
    protected function fiscalBuilder(Container $container): FiscalConfirmBuilder
    {
        $receipt = (new ParserReceiptFromBase64())->parse(base64_encode(json_encode($this->receiptData())))[0];
        return $container->get(FiscalConfirmBuilder::class)
            ->setShopId('shop123')->setOrderId('order123')->setPassword('password')
            ->setSubtotal('20.00')->setReceipt($receipt);
    }
}
