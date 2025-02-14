<?php
/**
 * Created by gitkv.
 * E-mail: gitkv@ya.ru
 * GitHub: gitkv
 */

namespace Tmconsulting\Uniteller\Signature;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Tmconsulting\Uniteller\Builder\BuilderInterface;
use Tmconsulting\Uniteller\Common\NameFieldsUniteller;

/**
 * Class Signature
 *
 * @package Tmconsulting\Client
 */
final class SignaturePayment extends AbstractSignature implements SignatureInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Create signature
     *
     * @param \Tmconsulting\Uniteller\Payment\PaymentBuilder $builder
     *
     * @return string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function create(BuilderInterface $builder): string
    {
        $parameters = [
            NameFieldsUniteller::SHOP_IDP => $builder->getShopId(),
            NameFieldsUniteller::ORDER_IDP => $builder->getOrderIdp(),
            NameFieldsUniteller::SUBTOTAL_P => $builder->getSubtotalP(),
            NameFieldsUniteller::MEAN_TYPE => $builder->getMeanType(),
            NameFieldsUniteller::E_MONEY_TYPE => $builder->getEMoneyType(),
            NameFieldsUniteller::LIFETIME => $builder->getLifetime(),
            NameFieldsUniteller::CUSTOMER_IDP => $builder->getCustomerIdp(),
            NameFieldsUniteller::CARD_IDP => $builder->getCardIdp(),
            NameFieldsUniteller::I_DATA => $builder->getIData(),
            NameFieldsUniteller::PT_CODE => $builder->getPtCode(),
        ];
        /**
         * The sequence of the fields below is very important !
         */
        if ($builder->getOrderLifetime() !== null) {
            $parameters[NameFieldsUniteller::ORDER_LIFETIME] = $builder->getOrderLifetime();
        }
        if ($builder->getPhoneVerified() !== null) {
            $parameters[NameFieldsUniteller::PHONE_VERIFIED] = $builder->getPhoneVerified();
        }
        if ($builder->getPaymentTypeLimits() !== null) {
            $parameters[NameFieldsUniteller::PAYMENT_TYPE_LIMITS] = $builder->getPaymentTypeLimits();
        }
        if ($builder->getMerchantOrderId() !== null) {
            $parameters[NameFieldsUniteller::MERCHANT_ORDER_ID] = $builder->getMerchantOrderId();
        }
        $parameters[NameFieldsUniteller::PASSWORD] = $builder->getPassword();

        $string = implode('&', array_map(static function ($item) {
            return md5($item ?? '');
        }, $parameters));

        $this->debugLogSignatureCalculation($parameters, $string);

        return strtoupper(md5($string));
    }

    /**
     * @param array $parameters Fields including in signature creation.
     * @param string $hash Middle not fully finished hash.
     */
    private function debugLogSignatureCalculation(array $parameters, string $hash)
    {
        if ($this->logger) {
            $str = 'Parameters including in signature: ' . PHP_EOL . print_r($parameters, true) . PHP_EOL
                . 'Calculation: ' . PHP_EOL;
            $arr1 = explode('&', $hash);
            $arr2 = [];
            $i = 0;
            foreach ($parameters as $k => $v) {
                $arr2['md5("' . $k . '")'] = $arr1[$i];
                ++$i;
            }
            $str .= print_r($arr2, true) . PHP_EOL;
            $str .= 'strtoupper(md5("' . $hash . '"))' . PHP_EOL;
            $this->logger->debug($str);
        }
    }
}
