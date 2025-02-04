<?php

namespace Tmconsulting\Uniteller\Request;

use Mtownsend\XmlToArray\XmlToArray;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tmconsulting\Uniteller\Exception\ExceptionFactory;
use Tmconsulting\Uniteller\Order\Order;

class ParserXml implements ParserInterface
{
    /**
     * @var \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64
     */
    private $parserReceipt;

    /**
     * @param \Tmconsulting\Uniteller\Request\ParserReceiptFromBase64 $parserReceipt
     */
    public function __construct(ParserReceiptFromBase64 $parserReceipt)
    {
        $this->parserReceipt = $parserReceipt;
    }

    /**
     * @param string $response
     *
     * @return array
     */
    public function parse(string $response): array
    {
        if (empty($response)) {
            return [];
        }
        return XmlToArray::convert($response);
    }

    /**
     * @param array $data
     *
     * @return \Tmconsulting\Uniteller\Order\Order[]
     *
     * @throws \Exception
     */
    public function parseOrders(array $data): array
    {
        $orders = [];
        if (!empty($data['orders']['order'])) {
            $firstKey = key($data['orders']['order']);
            if (!is_numeric($firstKey)) {
                $data['orders']['order'] = [$data['orders']['order']];
            }
            foreach ($data['orders']['order'] as $item) {
                $receipts = [];
                if (!empty($item['receipt'])) {
                    $receipts = $this->parserReceipt->parse($item['receipt']);
                }
                $orders[] = (new Order())
                    ->setOrderNumber((!empty($item['ordernumber']) ? $item['ordernumber'] : ''))
                    ->setResponseCode((!empty($item['response_code']) ? $item['response_code'] : ''))
                    ->setRecommendation((!empty($item['recommendation']) ? $item['recommendation'] : ''))
                    ->setMessage((!empty($item['message']) ? $item['message'] : ''))
                    ->setComment((!empty($item['comment']) ? $item['comment'] : ''))
                    ->setDate((!empty($item['date']) ? $item['date'] : null))
                    ->setTotal((!empty($item['total']) ? $item['total'] : ''))
                    ->setCurrency((!empty($item['currency']) ? $item['currency'] : ''))
                    ->setCardType((!empty($item['cardtype']) ? $item['cardtype'] : ''))
                    ->setCardNumber((!empty($item['cardnumber']) ? $item['cardnumber'] : ''))
                    ->setLastName((!empty($item['lastname']) ? $item['lastname'] : ''))
                    ->setFirstName((!empty($item['firstname']) ? $item['firstname'] : ''))
                    ->setMiddleName((!empty($item['middlename']) ? $item['middlename'] : ''))
                    ->setAddress((!empty($item['address']) ? $item['address'] : ''))
                    ->setEmail((!empty($item['email']) ? $item['email'] : ''))
                    ->setCountry((!empty($item['country']) ? $item['country'] : ''))
                    ->setRate((!empty($item['rate']) ? $item['rate'] : ''))
                    ->setApprovalCode((!empty($item['approvalcode']) ? $item['approvalcode'] : ''))
                    ->setCardSubType((!empty($item['cardsubtype']) ? $item['cardsubtype'] : ''))
                    ->setCvc2((bool)(!empty($item['cvc2']) ? $item['cvc2'] : false))
                    ->setCardHolder((!empty($item['cardholder']) ? $item['cardholder'] : ''))
                    ->setIp((!empty($item['ipaddress']) ? $item['ipaddress'] : ''))
                    ->setProtocolTypeName((!empty($item['protocoltypename']) ? $item['protocoltypename'] : ''))
                    ->setBillNumber((int)(!empty($item['billnumber']) ? $item['billnumber'] : 0))
                    ->setBankName((!empty($item['bankname']) ? $item['bankname'] : ''))
                    ->setStatus((string)(!empty($item['status']) ? $item['status'] : ''))
                    ->setErrorCode((int)(!empty($item['error_code']) ? $item['error_code'] : 0))
                    ->setErrorComment((!empty($item['error_comment']) ? $item['error_comment'] : ''))
                    ->setProcessingName((!empty($item['processingname']) ? $item['processingname'] : ''))
                    ->setPacketDate((!empty($item['packetdate']) ? $item['packetdate'] : ''))
                    ->setPaymentType((int)(!empty($item['paymenttype']) ? $item['paymenttype'] : 0))
                    ->setPhone((!empty($item['phone']) ? $item['phone'] : ''))
                    ->setIData((!empty($item['idata']) ? $item['idata'] : ''))
                    ->setPtCode((!empty($item['pt_code']) ? $item['pt_code'] : ''))
                    ->setEMoneyType((!empty($item['emoneytype']) ? $item['emoneytype'] : ''))
                    ->setEOrderData((!empty($item['eorderdata']) ? $item['eorderdata'] : ''))
                    ->setCardIdp((!empty($item['card_idp']) ? $item['card_idp'] : ''))
                    ->setAcquirerID((!empty($item['acquirerid']) ? $item['acquirerid'] : ''))
                    ->setIsOtherCard((bool)(!empty($item['isothercard']) ? $item['isothercard'] : false))
                    ->setParentOrderNumber((!empty($item['parent_order_number']) ? $item['parent_order_number'] : ''))
                    ->setNeedConfirm((bool)(!empty($item['need_confirm']) ? $item['need_confirm'] : false))
                    ->setGdsPaymentPurposeId((int)(!empty($item['firstname']) ? $item['firstname'] : 0))
                    ->setBookingcomId((!empty($item['bookingcom_id']) ? $item['bookingcom_id'] : ''))
                    ->setBookingcomPincode((!empty($item['bookingcom_pincode']) ? $item['bookingcom_pincode'] : ''))
                    ->setLoanId((!empty($item['loan_id']) ? $item['loan_id'] : ''))
                    ->setQrcId((!empty($item['qrcid']) ? $item['qrcid'] : ''))
                    ->setReceipts($receipts)
                    ->setSberOrderId((!empty($item['sberorderid']) ? $item['sberorderid'] : ''))
                    ->setSum((float)(!empty($item['sum']) ? $item['sum'] : 0))
                    ->setSignature((!empty($item['signature']) ? $item['signature'] : ''));
            }
        }
        return $orders;
    }

    public function parseResults(array $data): array
    {
        if (!empty($data['Receipt'])) {
            $data['Receipt'] = $this->parserReceipt->parse($data['Receipt']);
        }
        return $data;
    }

    public function parseErrors($data, RequestInterface $request, ResponseInterface $response)
    {
        if (isset($data['ErrorMessage']) && isset($data['Result'])) {
            throw ExceptionFactory::create($data['Result'], $data['ErrorMessage'], $request, $response);
        }
        if (isset($data['ErrorMessage'])) {
            throw ExceptionFactory::createFromMessage($data['ErrorMessage'], $request, $response);
        }
    }
}
