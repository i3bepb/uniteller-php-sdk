<?php
/**
 * Created by gitkv.
 * E-mail: gitkv@ya.ru
 * GitHub: gitkv
 */

namespace Tmconsulting\Uniteller\Signature;

use Tmconsulting\Uniteller\Common\NameFieldsUniteller;

/**
 * Class Signature
 *
 * @package Tmconsulting\Client
 */
final class SignaturePayment extends AbstractSignature
{
    /**
     * Create signature
     *
     * @param \Tmconsulting\Uniteller\Payment\PaymentBuilder $parameters
     *
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function create($parameters): string
    {
        $arr = [
            NameFieldsUniteller::SHOP_IDP => $parameters->getShopIdp(),
            NameFieldsUniteller::ORDER_IDP => $parameters->getOrderIdp(),
            NameFieldsUniteller::SUBTOTAL_P => $parameters->getSubtotalP(),
            NameFieldsUniteller::MEAN_TYPE => $parameters->getMeanType(),
            NameFieldsUniteller::E_MONEY_TYPE => $parameters->getEMoneyType(),
            NameFieldsUniteller::LIFETIME => $parameters->getLifetime(),
            NameFieldsUniteller::CUSTOMER_IDP => $parameters->getCustomerIdp(),
            NameFieldsUniteller::CARD_IDP => $parameters->getCardIdp(),
            NameFieldsUniteller::I_DATA => $parameters->getIData(),
            NameFieldsUniteller::PT_CODE => $parameters->getPtCode(),
        ];
        if ($parameters->getOrderLifetime() !== null) {
            $arr[NameFieldsUniteller::ORDER_LIFETIME] = $parameters->getOrderLifetime();
        }
        if ($parameters->getPhoneVerified() !== null) {
            $arr[NameFieldsUniteller::PHONE_VERIFIED] = $parameters->getPhoneVerified();
        }
        if ($parameters->getMerchantOrderId() !== null) {
            $arr[NameFieldsUniteller::MERCHANT_ORDER_ID] = $parameters->getMerchantOrderId();
        }
        if ($parameters->getPaymentTypeLimits() !== null) {
            $arr[NameFieldsUniteller::PAYMENT_TYPE_LIMITS] = $parameters->getPaymentTypeLimits();
        }
        $arr[NameFieldsUniteller::PASSWORD] = $parameters->getPassword();
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
        $this->showFormula($arr);
        $string = implode('&', array_map(static function ($item) {
            return md5($item ?? '');
        }, $arr));

        return strtoupper(md5($string));
    }

    private function showFormula(array $arr)
    {
        $newArr = [];
        $newArr2 = [];
        foreach ($arr as $k => $v) {
            $newArr[] = 'md5(' . $k . ')';
            $newArr2[] = 'md5(' . ($v ?? '') . ')';
        }
        echo '<pre>';
        echo 'strtoupper(md5(' . implode(' + & + ', $newArr) . '))';
        echo '</pre>';
        echo '<pre>';
        echo 'strtoupper(md5(' . implode(' + & + ', $newArr2) . '))';
        echo '</pre>';
        $string = implode('&', array_map(static function ($item) {
            return md5($item ?? '');
        }, $arr));
        echo '<pre>';
        echo 'strtoupper(md5("' . $string . '"))';
        echo '</pre>';
    }
}
