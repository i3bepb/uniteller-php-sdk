<?php

namespace Tmconsulting\Uniteller\Tests\Signature;

use Tmconsulting\Uniteller\Common\EMoneyType;
use Tmconsulting\Uniteller\Common\MeanType;
use Tmconsulting\Uniteller\Payment\CallbackFields;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Signature\SignaturePayment;
use Tmconsulting\Uniteller\Tests\TestCase;

class SignaturePaymentTest extends TestCase
{
    public static function dataProviderCreate(): array
    {
        return [
            [
                (new PaymentBuilder())
                    ->setShopId('00030576')
                    ->setOrderIdp(999)
                    ->setSubtotalP(50)
                    ->setPassword('password'),
                '473A2DAB9F207DC6B1CFF561677BDC8A'
            ],
            [
                (new PaymentBuilder())
                    ->setShopId('0099999')
                    ->setPassword('qwert12345')
                    ->setOrderIdp(25)
                    ->setSubtotalP(1000)
                    ->setCustomerIdp(12)
                    ->setLifetime(100)
                    ->setMeanType(MeanType::VISA)
                    ->setEMoneyType(EMoneyType::YANDEX_MONEY)
                    ->setCardIdp('4000000000002487'),
                'D88A16459439A2F1B6989570F152AF68'
            ],
            [
                (new PaymentBuilder())
                    ->setShopId('10030576')
                    ->setPassword('dpdmIXkHQ2GCGb46imY4AruqMg')
                    ->setOrderIdp(999)
                    ->setSubtotalP(50)
                    ->setMeanType(MeanType::VISA)
                    ->setEMoneyType(EMoneyType::ANY)
                    ->setMerchantOrderId('59c93853-4ba5-4552-b225-cc3ee0fe40ad')
                    ->setCallbackFields([CallbackFields::CARD_IDP, CallbackFields::CARD_NUMBER, CallbackFields::BILL_NUMBER])
                    ->setLifetime(100),
                'CE449F987E8F613634A92D693CC511DB'
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCreate
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testCreate(PaymentBuilder $builder, string $hash)
    {
        $signature = new SignaturePayment();
        $this->assertEquals($hash, $signature->create($builder));
    }
}
