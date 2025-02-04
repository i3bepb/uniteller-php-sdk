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
use Tmconsulting\Uniteller\Receipt\Item;
use Tmconsulting\Uniteller\Receipt\Item\Agent;
use Tmconsulting\Uniteller\Receipt\Item\Product;
use Tmconsulting\Uniteller\Receipt\Item\QtyPart;
use Tmconsulting\Uniteller\Receipt\Params;
use Tmconsulting\Uniteller\Receipt\PaymentInfo;

class ParserReceiptFromBase64
{
    /**
     * @param string $base64
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
        foreach ($arr as $data) {
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
                    $lines[] = new Item(
                        $line['name'],
                        $line['price'],
                        (int)$line['qty'],
                        (int)$line['unit'],
                        $line['sum'],
                        (int)$line['vat'],
                        (int)$line['payattr'],
                        (int)$line['lineattr'],
                        $product,
                        $agent,
                        $qtyPart
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
                        (float)$payment['amount'],
                        $payment['id'] ?? null
                    );
                }
            }

            $optionalFiscal = null;
            if (!empty($data['fiscal']['optional'])) {
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
            $paramsFiscal = null;
            if (!empty($data['fiscal']['params']['place'])) {
                $paramsFiscal = new Params($data['fiscal']['params']['place']);
            }
            $fiscal = new Fiscal(
                $data['fiscal']['id'],
                $data['fiscal']['date'],
                (int)$data['fiscal']['type'],
                new ElectronicCashRegister($data['fiscal']['ecr']['sn'], $data['fiscal']['ecr']['rn'], $data['fiscal']['ecr']['fs']),
                new Company($data['fiscal']['company']['name'], (int)$data['fiscal']['company']['inn']),
                new FiscalDataOperator($data['fiscal']['fdo']['name'], $data['fiscal']['fdo']['inn'], $data['fiscal']['fdo']['www']),
                $register,
                $optionalFiscal,
                $paramsFiscal
            );
            $receipts[] = new FiscalReceipt(
                $fiscal,
                (int)$data['taxmode'],
                $lines,
                $payments,
                $data['total'] ?? 0,
                $senderEmail,
                $optional,
                $customer,
                $cashier,
                $params,
                $userrequisite
            );
        }
        return $receipts;
    }
}
