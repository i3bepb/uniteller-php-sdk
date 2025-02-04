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
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
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
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $url = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
        $this->logger->info('Request from Uniteller: ' . $url, [
            'datetime'   => date('Y-m-d H:i:s'),
            'post_data'  => $_POST,
            'get_query'  => $_GET,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '?.?.?.?',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ]);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['Order_ID']) || empty($_POST['Status']) || empty($_POST['Signature'])) {
                $this->exitWithMessage('Not found parameters', 400);
            }
            $order = new CallbackOrder(
                $_POST['Order_ID'],
                $_POST['Status'],
                $_POST['AcquirerID'] ?? null,
                $_POST['ApprovalCode'] ?? null,
                $_POST['BillNumber'] ?? null,
                $_POST['Card_IDP'] ?? null,
                $_POST['CardNumber'] ?? null,
                $_POST['Customer_IDP'] ?? null,
                $_POST['ECI'] ?? null,
                $_POST['EMoneyType'] ?? null,
                $_POST['PaymentType'] ?? null,
                $_POST['Total'] ?? null,
                $_POST['Balance'] ?? null
            );
            if (!$this->signatureCreator->setParameters(array_merge($order->toArray(), [$this->getPassword()]))->verify($_POST['Signature'])) {
                $this->exitWithMessage('Signature not valid', 403);
            }
            $order->setSignature($_POST['Signature']);
            return $order;
        } else {
            $this->exitWithMessage('Method Not Allowed', 405);
        }
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
