<?php

namespace Tmconsulting\Uniteller\Callback;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Dependency\ContainerAwareInterface;
use Tmconsulting\Uniteller\Dependency\ContainerAwareTrait;
use Tmconsulting\Uniteller\Dependency\DebugAwareInterface;
use Tmconsulting\Uniteller\Dependency\DebugAwareTrait;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Exception\UnitellerRuntimeException;
use Tmconsulting\Uniteller\Order\CallbackOrder;
use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Signature\Signature;

/**
 * Обработчик callback-уведомлений Uniteller.
 *
 * Класс предназначен для:
 *  - парсинга входящего HTTP-запроса от Uniteller;
 *  - проверки подписи (Signature);
 *  - формирования объекта CallbackOrder с данными уведомления.
 *
 * Callback (webhook) отправляется платёжной системой Uniteller при изменении статуса заказа (оплата, отмена, возврат и т.д.).
 */
class Callback implements LoggerAwareInterface, DebugAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use DebugAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var \Tmconsulting\Uniteller\Signature\Signature
     */
    protected $signatureCreator;

    /**
     * Пароль. Доступен Мерчанту в Личном кабинете, пункт меню «Параметры Авторизации».
     *
     * @var string
     */
    protected $password;

    /**
     * @param \Tmconsulting\Uniteller\Signature\Signature $signatureCreator
     */
    public function __construct(Signature $signatureCreator)
    {
        $this->signatureCreator = $signatureCreator;
    }

    /**
     * @param string $password Пароль
     *
     * @return $this
     */
    public function setPassword(string $password): Callback
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function getPassword(): string
    {
        if (empty($this->password)) {
            throw new RequiredParameterException(UnitellerParameterName::PASSWORD);
        }
        return $this->password;
    }

    /**
     * @return \Tmconsulting\Uniteller\Order\CallbackOrder
     *
     * @throws \Tmconsulting\Uniteller\Exception\UnitellerRuntimeException
     */
    public function process(): CallbackOrder
    {
        $this->logRequest();

        $this->validateRequest($_POST);

        $password = $this->getPassword();

        $this->verifySignature($_POST, $password);

        $receipts = $this->parseReceipts($_POST, $password);

        return $this->createCallbackOrder($_POST, $receipts);
    }

    private function logRequest(): void
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $url = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
        $this->logger->info('Request from Uniteller: ' . $url, [
            'datetime'   => date('Y-m-d H:i:s'),
            'post_data'  => $_POST,
            'raw_post'   => file_get_contents('php://input'),
            'get_query'  => $_GET,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '?.?.?.?',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
    }

    private function validateRequest(array $post): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->exitWithMessage('Method Not Allowed', 405);
        }
        if (empty($post['Order_ID']) || empty($post['Status']) || empty($post['Signature'])) {
            $this->exitWithMessage('Not found parameters', 400);
        }
        if ($this->isFiscalCallback($post) !== array_key_exists('ReceiptSignature', $post)) {
            $this->exitWithMessage('Receipt and ReceiptSignature must be provided together', 400);
        }

        $fields = array_merge($this->getSignatureFields($post), ['Signature', 'Receipt', 'ReceiptSignature']);
        foreach ($fields as $field) {
            if (array_key_exists($field, $post) && !is_string($post[$field])) {
                $this->exitWithMessage('Invalid callback parameter: ' . $field, 400);
            }
        }
    }

    private function isFiscalCallback(array $post): bool
    {
        return array_key_exists('Receipt', $post);
    }

    private function getSignatureFields(array $post): array
    {
        if ($this->isFiscalCallback($post)) {
            // В фискальном callback сохраняем порядок полей входящего POST, даже при пустом Receipt.
            return array_intersect(
                array_keys($post),
                array_merge(['Order_ID', 'Status'], CallbackFields::toArray())
            );
        }

        // Обычный callback использует порядок из CallbackFieldsParameter.
        return array_merge(['Order_ID', 'Status'], CallbackFieldsSorter::sort(CallbackFields::toArray()));
    }

    private function verifySignature(array $post, string $password): void
    {
        // Подпись проверяется по исходным строкам до нормализации данных в CallbackOrder.
        $parameters = [];
        foreach ($this->getSignatureFields($post) as $field) {
            if (isset($post[$field])) {
                $parameters[$field] = $post[$field];
            }
        }
        $parameters[UnitellerParameterName::PASSWORD] = $password;
        if (!$this->signatureCreator->setParameters($parameters)->verify($post['Signature'])) {
            $this->exitWithMessage('Signature not valid', 403);
        }
    }

    private function verifyReceiptSignature(array $post, string $password): void
    {
        $expected = strtoupper(hash('sha256', $post['Order_ID'] . $post['Status'] . $post['Receipt'] . $password));
        if (!hash_equals($expected, strtoupper($post['ReceiptSignature']))) {
            $this->exitWithMessage('Receipt signature not valid', 403);
        }
    }

    /**
     * @return \Tmconsulting\Uniteller\Receipt\FiscalReceipt[]
     */
    private function parseReceipts(array $post, string $password): array
    {
        if (!$this->isFiscalCallback($post)) {
            return [];
        }

        $this->verifyReceiptSignature($post, $password);

        if ($post['Receipt'] === '') { // При преавторизации без чека аванса Receipt пустой.
            return [];
        }

        $receipts = [];
        $parser = $this->container->get(ParserReceiptFromBase64::class);
        try {
            $receipts = $parser->parse($post['Receipt']);
        } catch (\RuntimeException $e) {
            $this->logger->error('Receipt parsing failed', ['exception' => $e]);
            $this->exitWithMessage('Invalid receipt', 400);
        }

        return $receipts;
    }

    /**
     * @param \Tmconsulting\Uniteller\Receipt\FiscalReceipt[] $receipts
     */
    private function createCallbackOrder(array $post, array $receipts): CallbackOrder
    {
        $order = new CallbackOrder(
            $post['Order_ID'],
            $post['Status'],
            $post['AcquirerID'] ?? null,
            $post['ApprovalCode'] ?? null,
            $post['BillNumber'] ?? null,
            $post['Card_IDP'] ?? null,
            $post['CardNumber'] ?? null,
            $post['Customer_IDP'] ?? null,
            $post['ECI'] ?? null,
            $post['EMoneyType'] ?? null,
            $post['PaymentType'] ?? null,
            $post['Total'] ?? null,
            $post['Balance'] ?? null,
            $receipts,
            $post['ReceiptSignature'] ?? null
        );
        $order->setSignature($post['Signature']);

        return $order;
    }

    /**
     * @param string $message
     * @param int $code
     *
     * @throws \Tmconsulting\Uniteller\Exception\UnitellerRuntimeException
     */
    protected function exitWithMessage(string $message, int $code)
    {
        $this->logger->error($message);
        throw new UnitellerRuntimeException($message, $code);
    }
}
