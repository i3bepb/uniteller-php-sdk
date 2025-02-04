<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Payment\CallbackFields;
use Tmconsulting\Uniteller\Payment\Currency;
use Tmconsulting\Uniteller\Payment\EMoneyType;
use Tmconsulting\Uniteller\Payment\Language;
use Tmconsulting\Uniteller\Payment\MeanType;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Payment\PaymentType;
use Tmconsulting\Uniteller\Tests\TestCase;

class PaymentBuilderTest extends TestCase
{
    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdp()
    {
        $builder = new PaymentBuilder();
        $builder->setShopIdp('0009999');
        $this->assertEquals('0009999', $builder->getShopIdp());
    }

    /**
     * Set empty string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdpEmptyString()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Shop_IDP empty or not set');
        $builder = new PaymentBuilder();
        $builder->setShopIdp('');
        $builder->getShopIdp();
    }

    /**
     * Not set shop id
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetShopIdpNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Shop_IDP empty or not set');
        $builder = new PaymentBuilder();
        $builder->getShopIdp();
    }

    public static function dataProviderGetOrderIdp(): array
    {
        return [
            ['my101', 'my101'],
            [101, '101'],
            ['53c0714c-b036-408c-aeb6-58eb50f71098', '53c0714c-b036-408c-aeb6-58eb50f71098'],
            [0, '0'],
        ];
    }

    /**
     * @dataProvider dataProviderGetOrderIdp
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetOrderIdp($set, string $get)
    {
        $builder = new PaymentBuilder();
        $builder->setOrderIdp($set);
        $this->assertEquals($get, $builder->getOrderIdp());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testSetOrderIdpMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter Order_IDP, max 127');
        $builder = new PaymentBuilder();
        $builder->setOrderIdp('73ec70c2c1904c25ac401c2e700c19fe53c0714cb036408caeb658eb50f71098d2f736d7aec14114b47f9ad9181265d22943a8c4b47040858baa44ca432f54ed');
    }

    /**
     * Set empty string
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testSetOrderIdpEmptyString()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Order_IDP empty or not set');
        $builder = new PaymentBuilder();
        $builder->setOrderIdp('');
        $builder->getOrderIdp();
    }

    /**
     * Not set shop id
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetOrderIdpNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Order_IDP empty or not set');
        $builder = new PaymentBuilder();
        $builder->getOrderIdp();
    }

    public static function dataProviderGetSubtotalP(): array
    {
        return [
            [10000, 10000],
            ['10000.05', 10000.05],
            [10000.05, 10000.05],
        ];
    }

    /**
     * @dataProvider dataProviderGetSubtotalP
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetSubtotalP($set, float $get)
    {
        $builder = new PaymentBuilder();
        $builder->setSubtotalP($set);
        $this->assertIsFloat($builder->getSubtotalP());
        $this->assertEquals($get, $builder->getSubtotalP());
    }

    public static function dataProviderSetSubtotalPEmpty(): array
    {
        return [
            [''],
            [0],
            [-1000],
        ];
    }

    /**
     * @dataProvider dataProviderSetSubtotalPEmpty
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testSetSubtotalPEmpty($val)
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Subtotal_P empty or not set');
        $builder = new PaymentBuilder();
        $builder->setSubtotalP($val);
        $builder->getSubtotalP();
    }

    public static function dataProviderGetUrlReturnEmpty(): array
    {
        return [
            [''],
        ];
    }

    /**
     * @dataProvider dataProviderGetUrlReturnEmpty
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetUrlReturnOkEmpty($val)
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter URL_RETURN_OK empty or not set');
        $builder = new PaymentBuilder();
        $builder->setUrlReturnOk($val);
        $builder->getUrlReturnOk();
    }

    /**
     * @dataProvider dataProviderGetUrlReturnEmpty
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetUrlReturnNoEmpty($val)
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter URL_RETURN_NO empty or not set');
        $builder = new PaymentBuilder();
        $builder->setUrlReturnNo($val);
        $builder->getUrlReturnNo();
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetUrlReturnOk()
    {
        $builder = new PaymentBuilder();
        $builder->setUrlReturnOk('https://google.com/pay?q=banana');
        $this->assertEquals('https://google.com/pay?q=banana', $builder->getUrlReturnOk());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetUrlReturnNo()
    {
        $builder = new PaymentBuilder();
        $builder->setUrlReturnNo('https://google.com/pay?q=banana');
        $this->assertEquals('https://google.com/pay?q=banana', $builder->getUrlReturnNo());
    }

    public static function dataProviderGetCurrency(): array
    {
        return [
            ['banana', null, true],
            [Currency::RUB, Currency::RUB, false],
            [Currency::UAH, Currency::UAH, false],
            [Currency::AZN, Currency::AZN, false],
            [Currency::KZT, Currency::KZT, false],
            [Currency::EUR, Currency::EUR, false],
            [Currency::KGS, Currency::KGS, false],
            [Currency::USD, Currency::USD, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetCurrency
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCurrency(string $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter Currency, must be one of the values: ' . implode(',', Currency::toArray()));
        }
        $builder = new PaymentBuilder();
        $builder->setCurrency($set);
        $this->assertEquals($get, $builder->getCurrency());
    }

    public function testGetCurrencyNotSet()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCurrency());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetEmail()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getEmail());
        $builder->setEmail('example@gmail.com');
        $this->assertEquals('example@gmail.com', $builder->getEmail());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetEmailMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $builder = new PaymentBuilder();
        $builder->setEmail('abcxzqwertyuiopasdfghjklzxcvbnmexample@veryverylongdomainmail.com');
    }

    public function testGetLifetime()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getLifetime());
        $builder->setLifetime(200);
        $this->assertEquals(200, $builder->getLifetime());
    }

    public function testGetOrderLifetime()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getOrderLifetime());
        $builder->setOrderLifetime(200);
        $this->assertEquals(200, $builder->getOrderLifetime());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCustomerIdp()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCustomerIdp());
        $builder->setCustomerIdp(200);
        $this->assertEquals('200', $builder->getCustomerIdp());
        $builder->setCustomerIdp('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8');
        $this->assertEquals('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8', $builder->getCustomerIdp());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCustomerIdpMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $builder = new PaymentBuilder();
        $builder->setCustomerIdp('abcxzqwertyuiopasdfghjklzxcvbnmexampleveryverylongdomainmail50001');
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCardIdp()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCardIdp());
        $builder->setCardIdp(200);
        $this->assertEquals('200', $builder->getCardIdp());
        $builder->setCardIdp('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8');
        $this->assertEquals('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8', $builder->getCardIdp());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCardIdpMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $builder = new PaymentBuilder();
        $builder->setCardIdp('abcxzqwertyuiopasdfghjklzxcvbnmexampleveryverylongdomainmail50002abcxzqwertyuiopasdfghjklzxcvbnmexampleveryverylongdomainmail50002');
    }

    public function testGetPtCode()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getPtCode());
        $builder->setPtCode('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8');
        $this->assertEquals('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8', $builder->getPtCode());
    }

    public function testGetMeanTypeNotSet()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getMeanType());
    }

    public static function dataProviderGetMeanType(): array
    {
        return [
            [100, null, true],
            [MeanType::ANY, MeanType::ANY, false],
            [MeanType::VISA, MeanType::VISA, false],
            [MeanType::MASTERCARD, MeanType::MASTERCARD, false],
            [MeanType::DINERS_CLUB, MeanType::DINERS_CLUB, false],
            [MeanType::JCB, MeanType::JCB, false],
            [MeanType::AMERICAN_EXPRESS, MeanType::AMERICAN_EXPRESS, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetMeanType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetMeanType(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter MeanType, must be one of the values: ' . implode(',', MeanType::toArray()));
        }
        $builder = new PaymentBuilder();
        $builder->setMeanType($set);
        $this->assertEquals($get, $builder->getMeanType());
    }

    public function testGetEMoneyTypeNotSet()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getEMoneyType());
    }

    public static function dataProviderGetEMoneyType(): array
    {
        return [
            [100, null, true],
            [EMoneyType::ANY, EMoneyType::ANY, false],
            [EMoneyType::YANDEX_MONEY, EMoneyType::YANDEX_MONEY, false],
            [EMoneyType::CASH, EMoneyType::CASH, false],
            [EMoneyType::QIWI_REST, EMoneyType::QIWI_REST, false],
            [EMoneyType::MOBI, EMoneyType::MOBI, false],
            [EMoneyType::WEBMONEY_WMR, EMoneyType::WEBMONEY_WMR, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetEMoneyType
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetEMoneyType(int $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter EMoneyType, must be one of the values: ' . implode(',', EMoneyType::toArray()));
        }
        $builder = new PaymentBuilder();
        $builder->setEMoneyType($set);
        $this->assertEquals($get, $builder->getEMoneyType());
    }

    public function testGetBillLifetimeNotSet()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getBillLifetime());
    }

    public function testGetBillLifetime()
    {
        $builder = new PaymentBuilder();
        $builder->setBillLifetime(72);
        $this->assertEquals(72, $builder->getBillLifetime());
    }

    public function testGetPreAuth()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->isPreAuth());
        $builder->usePreAuth();
        $this->assertEquals('1', $builder->isPreAuth());
    }

    public function testIsRecurrentStart()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->isIsRecurrentStart());
        $builder->useRecurrentPayment();
        $this->assertEquals('1', $builder->isIsRecurrentStart());
    }

    public function testGetCallbackFields()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCallbackFields());
        $builder->setCallbackFields([CallbackFields::E_MONEY_TYPE, CallbackFields::CARD_IDP]);
        $this->assertEquals('EMoneyType Card_IDP', $builder->getCallbackFields());
    }

    public function testGetCallbackFormat()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCallbackFormat());
        $builder->setCallbackFormat('json');
        $this->assertEquals('json', $builder->getCallbackFormat());
    }

    public function testGetLanguageNotSet()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getLanguage());
    }

    public static function dataProviderGetLanguage(): array
    {
        return [
            ['kzn', null, true],
            [Language::RU, Language::RU, false],
            [Language::EN, Language::EN, false],
        ];
    }

    /**
     * @dataProvider dataProviderGetLanguage
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetLanguage(string $set, $get, bool $exception)
    {
        if ($exception) {
            $this->expectException(NotValidParameterException::class);
            $this->expectExceptionMessage('Not valid parameter Language, must be one of the values: ' . implode(',', Language::toArray()));
        }
        $builder = new PaymentBuilder();
        $builder->setLanguage($set);
        $this->assertEquals($get, $builder->getLanguage());
    }

    public function testGetComment()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getComment());
        $builder->setComment('Very good order!');
        $this->assertEquals('Very good order!', $builder->getComment());
    }

    public function testGetFirstName()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getFirstName());
        $builder->setFirstName('Alexander');
        $this->assertEquals('Alexander', $builder->getFirstName());
    }

    public function testGetLastName()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getLastName());
        $builder->setLastName('Ivanov');
        $this->assertEquals('Ivanov', $builder->getLastName());
    }

    public function testGetMiddleName()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getMiddleName());
        $builder->setMiddleName('Ivanov');
        $this->assertEquals('Ivanov', $builder->getMiddleName());
    }

    public function testGetPhone()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getPhone());
        $builder->setPhone('+79509999999');
        $this->assertEquals('+79509999999', $builder->getPhone());
    }

    public function testGetAddress()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getAddress());
        $builder->setAddress('c.Патруши Сысертский р-н, Свердловская обл.');
        $this->assertEquals('c.Патруши Сысертский р-н, Свердловская обл.', $builder->getAddress());
    }

    public function testGetCountry()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCountry());
        $builder->setCountry('Россия');
        $this->assertEquals('Россия', $builder->getCountry());
    }

    public function testGetState()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCountry());
        $builder->setCountry('3422');
        $this->assertEquals('3422', $builder->getCountry());
    }

    public function testGetCity()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getCity());
        $builder->setCity('Москва');
        $this->assertEquals('Москва', $builder->getCity());
    }

    public function testGetZip()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getZip());
        $builder->setZip('D-80331 München');
        $this->assertEquals('D-80331 München', $builder->getZip());
        $builder->setZip(624003);
        $this->assertEquals('624003', $builder->getZip());
    }

    public function testGetPhoneVerified()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getPhoneVerified());
        $builder->setPhoneVerified('+79509999999');
        $this->assertEquals('+79509999999', $builder->getPhoneVerified());
    }

    public function testGetDestPhoneNum()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getDestPhoneNum());
        $builder->setDestPhoneNum('+79509999999');
        $this->assertEquals('+79509999999', $builder->getDestPhoneNum());
    }

    public function testGetMerchantOrderId()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getMerchantOrderId());
        $builder->setMerchantOrderId('7950');
        $this->assertEquals('7950', $builder->getMerchantOrderId());
        $builder->setMerchantOrderId(7950);
        $this->assertEquals('7950', $builder->getMerchantOrderId());
    }

    public static function dataProviderGetPaymentTypeLimits(): array
    {
        return [
            [
                [
                    PaymentType::FAST_PAYMENT_SYSTEM => [5000.3, 4000.7],
                    PaymentType::SBER_PAY => [12300.37],
                ],
                '{"13":[5000.3,4000.7],"14":[12300.37]}'
            ],
            ['{"' . PaymentType::BANK_CARD . '":[10000]}', '{"1":[10000]}'],
        ];
    }

    /**
     * @dataProvider dataProviderGetPaymentTypeLimits
     */
    public function testGetPaymentTypeLimits($set, string $get)
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getPaymentTypeLimits());
        $builder->setPaymentTypeLimits($set);
        $this->assertEquals($get, $builder->getPaymentTypeLimits());
    }

    public function testGetBackUrl()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getBackUrl());
        $builder->setBackUrl('https://google.com');
        $this->assertEquals('https://google.com', $builder->getBackUrl());
    }

    public function testGetDeepLink()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getDeepLink());
        $builder->setDeepLink('https://google.com');
        $this->assertEquals('https://google.com', $builder->getDeepLink());
    }

    public function testGetEWallet()
    {
        $builder = new PaymentBuilder();
        $this->assertNull($builder->getEWallet());
        $builder->setEWallet('47416810600000000004');
        $this->assertEquals('47416810600000000004', $builder->getEWallet());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetPasswordNotSet()
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Password empty or not set');
        $builder = new PaymentBuilder();
        $builder->getPassword();
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetPassword()
    {
        $builder = new PaymentBuilder();
        $builder->setPassword('password12345');
        $this->assertEquals('password12345', $builder->getPassword());
    }
}
