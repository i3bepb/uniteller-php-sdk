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
final class SignaturePayment extends AbstractSignature implements SignatureInterface
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

        $string = implode('&', array_map(static function ($item) {
            return md5($item ?? '');
        }, $arr));
        $this->logFormula($arr, $string);

        return strtoupper(md5($string));
    }

    /**
     * @param array $arr Fields involved in the signature
     * @param string $hash
     */
    private function logFormula(array $arr, string $hash)
    {
        if ($this->logger) {
            $str = 'Fields involved in the signature: ' . PHP_EOL . print_r($arr, true) . PHP_EOL . 'Formula: ' . PHP_EOL;
            $newArr = [];
            $newArr2 = [];
            foreach ($arr as $k => $v) {
                $newArr[] = 'md5(' . $k . ')';
                $newArr2[] = 'md5(' . ($v ?? '') . ')';
            }
            $str .= 'strtoupper(md5(' . implode(' + & + ', $newArr) . '))' . PHP_EOL;
            $str .= 'strtoupper(md5(' . implode(' + & + ', $newArr2) . '))' . PHP_EOL;
            $str .= 'strtoupper(md5("' . $hash . '"))' . PHP_EOL;
            $this->logger->debug($str);
        }
    }
}
