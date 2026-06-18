<?php

namespace Tmconsulting\Uniteller\Request;

use Tmconsulting\Uniteller\Receipt\Cashier;
use Tmconsulting\Uniteller\Receipt\Customer;
use Tmconsulting\Uniteller\Receipt\Fiscal;
use Tmconsulting\Uniteller\Receipt\Fiscal\Company;
use Tmconsulting\Uniteller\Receipt\Fiscal\ElectronicCashRegister;
use Tmconsulting\Uniteller\Receipt\Fiscal\FiscalDataOperator;
use Tmconsulting\Uniteller\Receipt\Fiscal\Register;
use Tmconsulting\Uniteller\Receipt\FiscalReceipt;
use Tmconsulting\Uniteller\Receipt\IndustryProps;
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\Item\Product;
use Tmconsulting\Uniteller\Receipt\Item\QtyPart;
use Tmconsulting\Uniteller\Receipt\Params;
use Tmconsulting\Uniteller\Receipt\PaymentInfo;

class ParserReceiptFromBase64
{
    /**
     * @param string $base64 Чек в формате base64.
     *
     * @return \Tmconsulting\Uniteller\Receipt\FiscalReceipt[]
     *
     * @throws \RuntimeException
     */
    public function parse(string $base64): array
    {
        $receipts = [];
        if (empty($base64)) {
            throw new \RuntimeException('Empty base64 receipt');
        }
        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            throw new \RuntimeException('Not valid base64 receipt');
        }
        $arr = json_decode($decoded, true);
        if ($arr === null) {
            throw new \RuntimeException('Not valid json receipt');
        }
        if (!is_array($arr)) {
            throw new \RuntimeException('Invalid receipt structure');
        }
        foreach ($arr as $data) {
            if (!is_array($data)) {
                throw new \RuntimeException('Invalid receipt structure');
            }
            $lines = [];
            if (!empty($data['lines'])) {
                foreach ($data['lines'] as $line) {
                    $product = null;
                    if (!empty($line['product'])) {
                        $product = new Product(
                            $line['product']['kt'],
                            $line['product']['exc'],
                            $line['product']['coc'],
                            $line['product']['ncd']
                        );
                    }
                    $agent = null;
                    if (!empty($line['agent'])) {
                        $agent = new Agent(
                            $line['agent']['agentattr'],
                            $line['agent']['agentphone'] ?? null,
                            $line['agent']['accopphone'] ?? null,
                            $line['agent']['opphone'] ?? null,
                            $line['agent']['opname'] ?? null,
                            $line['agent']['opinn'] ?? null,
                            $line['agent']['opaddress'] ?? null,
                            $line['agent']['operation'] ?? null,
                            $line['agent']['suppliername'] ?? null,
                            $line['agent']['supplierinn'] ?? null,
                            $line['agent']['supplierphone'] ?? null
                        );
                    }
                    $qtyPart = null;
                    if (!empty($line['qtypart'])) {
                        $qtyPart = new QtyPart(
                            (int)$line['qtypart']['numerator'],
                            (int)$line['qtypart']['denominator']
                        );
                    }
                    $industryProps = [];
                    if (!empty($line['industryProps']) && is_array($line['industryProps'])) {
                        foreach ($line['industryProps'] as $industryProp) {
                            $industryProps[] = new IndustryProps(
                                isset($industryProp['id']) ? (string)$industryProp['id'] : null,
                                isset($industryProp['date']) ? (string)$industryProp['date'] : null,
                                isset($industryProp['number']) ? (string)$industryProp['number'] : null,
                                isset($industryProp['values']) ? (string)$industryProp['values'] : null,
                                $industryProp
                            );
                        }
                    }
                    $lines[] = new Item(
                        $line['name'],
                        $line['price'],
                        (int)$line['qty'],
                        (empty($line['unit']) ? 0 : (int)$line['unit']),
                        $line['sum'],
                        (int)$line['vat'],
                        (int)$line['payattr'],
                        (int)$line['lineattr'],
                        $product,
                        $agent,
                        $qtyPart,
                        isset($line['goodstatus']) ? (int)$line['goodstatus'] : null,
                        $industryProps,
                        $line
                    );
                }
            }
            $customer = null;
            if (!empty($data['customer'])) {
                $customer = new Customer(
                    $data['customer']['phone'] ?? null,
                    $data['customer']['email'] ?? null,
                    $data['customer']['id'] ?? null,
                    $data['customer']['name'] ?? null,
                    $data['customer']['inn'] ?? null,
                    $data['customer']['birthday'] ?? null,
                    $data['customer']['citizenship'] ?? null,
                    $data['customer']['doccode'] ?? null,
                    $data['customer']['docdata'] ?? null,
                    $data['customer']['address'] ?? null
                );
            }
            $cashier = null;
            if (!empty($data['cashier'])) {
                $cashier = new Cashier(
                    $data['cashier']['name'] ?? null,
                    $data['cashier']['inn'] ?? null
                );
            }
            $optional = null;
            if (!empty($data['optional'])) {
                $optional = $data['optional'];
            }
            $params = null;
            if (!empty($data['params']['place'])) {
                $params = new Params($data['params']['place']);
            }
            $userrequisite = null;
            if (!empty($data['userrequisite'])) {
                $userrequisite = $data['userrequisite'];
            }
            $senderEmail = $data['senderemail'] ?? null;
            $payments = [];
            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $payment) {
                    $payments[] = new PaymentInfo(
                        (int)$payment['kind'],
                        (int)$payment['type'],
                        (string)$payment['amount'],
                        isset($payment['id']) ? (string)$payment['id'] : null,
                        $payment
                    );
                }
            }

            $optionalFiscal = null;
            if (!empty($data['fiscal']['optional']) && is_array($data['fiscal']['optional'])) {
                $optionalFiscal = $data['fiscal']['optional'];
            }
            $register = new Register(
                $data['fiscal']['register']['fiscal_number'],
                $data['fiscal']['register']['shift_number'],
                $data['fiscal']['register']['shift_index'],
                $data['fiscal']['register']['fiscal_date'],
                $data['fiscal']['register']['fiscal_attr'],
                $data['fiscal']['register']['fdo_date'],
                $data['fiscal']['register']['fdo_attr'],
                $data['fiscal']['register']['fiscal_link'] ?? null,
                $data['fiscal']['register']['qr'] ?? null,
                $data['fiscal']['register']['markinginfo'] ?? null
            );
            $fiscalParams = null;
            if (isset($data['fiscal']['params']['place'])) {
                $fiscalParams = new Params(
                    (string)$data['fiscal']['params']['place']
                );
            }
            $fiscal = new Fiscal(
                $data['fiscal']['id'],
                isset($data['fiscal']['date']) ? (string)$data['fiscal']['date'] : null,
                (int)$data['fiscal']['type'],
                new ElectronicCashRegister($data['fiscal']['ecr']['sn'], $data['fiscal']['ecr']['rn'], $data['fiscal']['ecr']['fs']),
                new Company($data['fiscal']['company']['name'], $data['fiscal']['company']['inn']),
                new FiscalDataOperator($data['fiscal']['fdo']['name'], $data['fiscal']['fdo']['inn'], $data['fiscal']['fdo']['www']),
                $register,
                $optionalFiscal,
                $fiscalParams
            );
            $industryProps = [];
            if (!empty($data['industryProps']) && is_array($data['industryProps'])) {
                foreach ($data['industryProps'] as $industryProp) {
                    $industryProps[] = new IndustryProps(
                        isset($industryProp['id']) ? (string)$industryProp['id'] : null,
                        isset($industryProp['date']) ? (string)$industryProp['date'] : null,
                        isset($industryProp['number']) ? (string)$industryProp['number'] : null,
                        isset($industryProp['values']) ? (string)$industryProp['values'] : null,
                        $industryProp
                    );
                }
            }
            $receipts[] = new FiscalReceipt(
                $data,
                $fiscal,
                (int)$data['taxmode'],
                $lines,
                $payments,
                $data['total'] ?? 0,
                $senderEmail,
                $optional,
                $customer,
                $cashier,
                isset($data['internet']) ? (int)$data['internet'] : null,
                isset($data['timezone']) ? (int)$data['timezone'] : null,
                $data['additionalRequisite'] ?? null,
                $params,
                $userrequisite,
                $industryProps,
                isset($data['correctionType']) ? (int)$data['correctionType'] : null,
                isset($data['causeDocumentNumber']) ? (string)$data['causeDocumentNumber'] : null,
                isset($data['causeDocumentDate']) ? (string)$data['causeDocumentDate'] : null,
                isset($data['correctionSubject']) ? (int)$data['correctionSubject'] : null
            );
        }
        return $receipts;
    }
}
