<?php

namespace Tmconsulting\Uniteller\Response;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ExceptionFactory;
use Tmconsulting\Uniteller\Order\Order;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Request\DecodedResponse;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;

/**
 * Разбирает заказы и ошибки CSV-ответов, сохраняя прежние правила заполнения полей.
 */
class LegacyCsvResponseParser implements LegacyResponseParserInterface
{
    /** @var ParserReceiptFromBase64 Парсер фискальных чеков заказа. */
    private $parserReceipt;

    /**
     * @param ParserReceiptFromBase64 $parserReceipt Парсер чеков в кодировке Base64.
     */
    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    /**
     * Проверяет ошибки Uniteller и преобразует данные ответа в заказы.
     *
     * @param DecodedResponse $response Декодированный CSV-ответ с исходными HTTP-сообщениями.
     *
     * @return \Tmconsulting\Uniteller\Order\Order[] Заказы с декодированными чеками.
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException Если ответ содержит ошибку Uniteller.
     * @throws \RuntimeException Если не удалось декодировать чеки.
     */
    public function parse(DecodedResponse $response): array
    {
        $data = $response->getData();
        $this->parseErrors($data, $response->getRequest(), $response->getResponse());

        return $this->parseOrders($data);
    }

    /**
     * Преобразует строки CSV в заказы.
     * Отсутствующее поле чека сохраняется как null.
     *
     * @param array $data Декодированные данные CSV-ответа.
     *
     * @return \Tmconsulting\Uniteller\Order\Order[] Заказы с прежними значениями по умолчанию.
     *
     * @throws \RuntimeException Если не удалось декодировать чеки.
     */
    protected function parseOrders(array $data): array
    {
        $arr = [];
        foreach ($data as $item) {
            $receipts = null;
            if (!empty($item[SFields::RECEIPT])) {
                $receipts = $this->parserReceipt->parse($item[SFields::RECEIPT]);
            }
            $order = (new Order())
                ->setOrderNumber($item[SFields::ORDER_NUMBER] ?? null)
                ->setResponseCode($item[SFields::RESPONSE_CODE] ?? null)
                ->setRecommendation($item[SFields::RECOMMENDATION] ?? null)
                ->setMessage($item[SFields::MESSAGE] ?? null)
                ->setComment($item[SFields::COMMENT] ?? null)
                ->setDate($item[SFields::DATE] ?? null)
                ->setTotal($item[SFields::TOTAL] ?? null)
                ->setCurrency($item[SFields::CURRENCY] ?? null)
                ->setCardType($item[SFields::CARD_TYPE] ?? null)
                ->setCardNumber($item[SFields::CARD_NUMBER] ?? null)
                ->setLastName($item[SFields::LAST_NAME] ?? null)
                ->setFirstName($item[SFields::FIRST_NAME] ?? null)
                ->setMiddleName($item[SFields::MIDDLE_NAME] ?? null)
                ->setAddress($item[SFields::ADDRESS] ?? null)
                ->setEmail($item[SFields::EMAIL] ?? null)
                ->setCountry($item[SFields::COUNTRY] ?? null)
                ->setRate($item[SFields::RATE] ?? null)
                ->setApprovalCode($item[SFields::APPROVAL_CODE] ?? null)
                ->setCardSubType($item[SFields::CARD_SUB_TYPE] ?? null)
                ->setCardHolder($item[SFields::CARD_HOLDER] ?? null)
                ->setIp($item[SFields::IP_ADDRESS] ?? null)
                ->setProtocolTypeName($item[SFields::PROTOCOL_TYPE_NAME] ?? null)
                ->setBillNumber($item[SFields::BILL_NUMBER] ?? null)
                ->setBankName($item[SFields::BANK_NAME] ?? null)
                ->setStatus($item[SFields::STATUS] ?? null)
                ->setErrorComment($item[SFields::ERROR_COMMENT] ?? null)
                ->setProcessingName($item[SFields::PROCESSING_NAME] ?? null)
                ->setPacketDate($item[SFields::PACKET_DATE] ?? null)
                ->setPhone($item[SFields::PHONE] ?? null)
                ->setIData($item[SFields::I_DATA] ?? null)
                ->setPtCode($item[SFields::PT_CODE] ?? null)
                ->setEMoneyType($item[SFields::E_MONEY_TYPE] ?? null)
                ->setEOrderData($item[SFields::E_ORDER_DATA] ?? null)
                ->setCardIdp($item[SFields::CARD_IDP] ?? null)
                ->setAcquirerID($item[SFields::ACQUIRER_ID] ?? null)
                ->setParentOrderNumber($item[SFields::PARENT_ORDER_NUMBER] ?? null)
                ->setBookingcomId($item[SFields::BOOKINGCOM_ID] ?? null)
                ->setBookingcomPincode($item[SFields::BOOKINGCOM_PINCODE] ?? null)
                ->setLoanId($item[SFields::LOAN_ID] ?? null)
                ->setQrcId($item[SFields::QRC_ID] ?? null)
                ->setReceipts($receipts)
                ->setSberOrderId($item[SFields::SBER_ORDER_ID] ?? null)
                ->setSum($item[SFields::SUM] ?? null)
                ->setTokenIdp($item[SFields::TOKEN_IDP] ?? null)
                ->setGiftCert($item[SFields::GIFT_CERT] ?? null)
                ->setOpkcID($item[SFields::OPKC_ID] ?? null)
                ->setTpayRequestId($item[SFields::TPAY_REQUEST_ID] ?? null)
                ->setLkOrderUrl($item[SFields::LK_ORDER_URL] ?? null)
                ->setBnplRequestId($item[SFields::BNPL_REQUEST_ID] ?? null);
            if (isset($item[SFields::CVC2])) {
                $order->setCvc2((int)$item[SFields::CVC2]);
            }
            if (isset($item[SFields::ERROR_CODE])) {
                $order->setErrorCode((int)$item[SFields::ERROR_CODE]);
            }
            if (isset($item[SFields::PAYMENT_TYPE])) {
                $order->setPaymentType((int)$item[SFields::PAYMENT_TYPE]);
            }
            if (isset($item[SFields::IS_OTHER_CARD])) {
                $order->setIsOtherCard((bool)$item[SFields::IS_OTHER_CARD]);
            }
            if (isset($item[SFields::NEED_CONFIRM])) {
                $order->setNeedConfirm((int)$item[SFields::NEED_CONFIRM]);
            }
            if (isset($item[SFields::GDS_PAYMENT_PURPOSE_ID])) {
                $order->setGdsPaymentPurposeId((int)$item[SFields::GDS_PAYMENT_PURPOSE_ID]);
            }
            $arr[] = $order;
        }

        return $arr;
    }

    /**
     * Проверяет ErrorMessage и ErrorCode в первой строке CSV-ответа.
     * При отсутствии кода подбирает исключение по тексту сообщения.
     *
     * @param array $data Декодированные строки CSV.
     * @param \Psr\Http\Message\RequestInterface $request Исходный HTTP-запрос.
     * @param \Psr\Http\Message\ResponseInterface $response Исходный HTTP-ответ.
     *
     * @return void
     *
     * @throws \Tmconsulting\Uniteller\Exception\ErrorException
     */
    protected function parseErrors($data, RequestInterface $request, ResponseInterface $response)
    {
        if (isset($data[0]['ErrorMessage']) && isset($data[0]['ErrorCode'])) {
            throw ExceptionFactory::create($data[0]['ErrorCode'], $data[0]['ErrorMessage'], $request, $response);
        }
        if (isset($data[0]['ErrorMessage'])) {
            throw ExceptionFactory::createFromMessage($data[0]['ErrorMessage'], $request, $response);
        }
    }
}
