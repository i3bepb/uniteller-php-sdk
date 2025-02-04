<?php

namespace Tmconsulting\Uniteller\Tests\Payment;

use Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Parameter\Enum\CallbackFields;
use Tmconsulting\Uniteller\Parameter\Enum\Currency;
use Tmconsulting\Uniteller\Parameter\Enum\EMoneyType;
use Tmconsulting\Uniteller\Parameter\Enum\Language;
use Tmconsulting\Uniteller\Parameter\Enum\MeanType;
use Tmconsulting\Uniteller\Parameter\Enum\PaymentType;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Payment\PaymentBuilder;
use Tmconsulting\Uniteller\Payment\Uri;
use Tmconsulting\Uniteller\Request\ApiEndpoints;
use Tmconsulting\Uniteller\Signature\SignatureInterface;
use Tmconsulting\Uniteller\Tests\TestCase;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Payment\PaymentBuilder
 */
class PaymentBuilderTest extends TestCase
{
    private $builder;

    protected function setUp(): void
    {
        $signatureCreator = $this->createMock(SignatureInterface::class);
        $signatureCreator->method('setFields')->willReturnSelf();
        $signatureCreator->method('createMd5')->willReturn('mocked_signature');
        $this->builder = new PaymentBuilder($signatureCreator);
    }

    public static function dataProviderGetOrderIdp(): array
    {
        return [
            ['my101', 'my101'],
            [101, '101'],
            ['53c0714c-b036-408c-aeb6-58eb50f71098', '53c0714c-b036-408c-aeb6-58eb50f71098'],
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
        $this->builder->setOrderId($set);
        $this->assertEquals($get, $this->builder->getOrderId());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testSetOrderIdpMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $this->expectExceptionMessage('Not valid parameter Order_IDP, max 127');
        $this->builder->setOrderId(str_repeat('a', 128));
    }

    public static function dataProviderGetOrderIdpEmpty(): array
    {
        return [
            [0],
            [''],
        ];
    }

    /**
     * @dataProvider dataProviderGetOrderIdpEmpty
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testSetOrderIdpEmptyString($set)
    {
        $this->expectException(RequiredParameterException::class);
        $this->expectExceptionMessage('Parameter Order_IDP empty or not set');
        $this->builder->setOrderId($set);
        $this->builder->getOrderId();
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
        $this->builder->getOrderId();
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
        $this->builder->setSubtotalP($set);
        $this->assertIsFloat($this->builder->getSubtotalP());
        $this->assertEquals($get, $this->builder->getSubtotalP());
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
        $this->builder->setUrlReturnOk($val);
        $this->builder->getUrlReturnOk();
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
        $this->builder->setUrlReturnNo($val);
        $this->builder->getUrlReturnNo();
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetUrlReturnOk()
    {
        $this->builder->setUrlReturnOk('https://google.com/pay?q=banana');
        $this->assertEquals('https://google.com/pay?q=banana', $this->builder->getUrlReturnOk());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testGetUrlReturnNo()
    {
        $this->builder->setUrlReturnNo('https://google.com/pay?q=banana');
        $this->assertEquals('https://google.com/pay?q=banana', $this->builder->getUrlReturnNo());
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
        $this->builder->setCurrency($set);
        $this->assertEquals($get, $this->builder->getCurrency());
    }

    public function testGetCurrencyNotSet()
    {
        $this->assertNull($this->builder->getCurrency());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetEmail()
    {
        $this->assertNull($this->builder->getEmail());
        $this->builder->setEmail('example@gmail.com');
        $this->assertEquals('example@gmail.com', $this->builder->getEmail());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetEmailMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setEmail('abcxzqwertyuiopasdfghjklzxcvbnmexample@veryverylongdomainmail.com');
    }

    public function testGetLifetime()
    {
        $this->assertNull($this->builder->getLifetime());
        $this->builder->setLifetime(200);
        $this->assertEquals(200, $this->builder->getLifetime());
    }

    public function testGetOrderLifetime()
    {
        $this->assertNull($this->builder->getOrderLifetime());
        $this->builder->setOrderLifetime(200);
        $this->assertEquals(200, $this->builder->getOrderLifetime());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCustomerIdp()
    {
        $this->assertNull($this->builder->getCustomerIdp());
        $this->builder->setCustomerIdp(200);
        $this->assertEquals('200', $this->builder->getCustomerIdp());
        $this->builder->setCustomerIdp('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8');
        $this->assertEquals('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8', $this->builder->getCustomerIdp());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCustomerIdpMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setCustomerIdp('abcxzqwertyuiopasdfghjklzxcvbnmexampleveryverylongdomainmail50001');
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCardIdp()
    {
        $this->assertNull($this->builder->getCardIdp());
        $this->builder->setCardIdp(200);
        $this->assertEquals('200', $this->builder->getCardIdp());
        $this->builder->setCardIdp('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8');
        $this->assertEquals('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8', $this->builder->getCardIdp());
    }

    /**
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     */
    public function testGetCardIdpMoreThan()
    {
        $this->expectException(NotValidParameterException::class);
        $this->builder->setCardIdp(str_repeat('a', 129));
    }

    public function testGetPtCode()
    {
        $this->assertNull($this->builder->getPtCode());
        $this->builder->setPtCode('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8');
        $this->assertEquals('4b7d00bc-b7ed-43bb-a3d1-4bcc66ad7cb8', $this->builder->getPtCode());
    }

    public function testGetBillLifetimeNotSet()
    {
        $this->assertNull($this->builder->getBillLifetime());
    }

    public function testGetBillLifetime()
    {
        $this->builder->setBillLifetime(72);
        $this->assertEquals(72, $this->builder->getBillLifetime());
    }

    public function testGetCallbackFields()
    {
        $this->assertNull($this->builder->getCallbackFields());
        $this->builder->setCallbackFields([CallbackFields::TOTAL, CallbackFields::BILL_NUMBER]);
        $this->assertEquals('BillNumber Total', $this->builder->getCallbackFields());
        $this->builder->setCallbackFields([CallbackFields::E_MONEY_TYPE, CallbackFields::CARD_IDP]);
        $this->assertEquals('Card_IDP EMoneyType', $this->builder->getCallbackFields());
    }

    public function testGetCallbackFormat()
    {
        $this->assertNull($this->builder->getCallbackFormat());
        $this->builder->setCallbackFormat('json');
        $this->assertEquals('json', $this->builder->getCallbackFormat());
    }

    public function testGetLanguageNotSet()
    {
        $this->assertNull($this->builder->getLanguage());
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
        $this->builder->setLanguage($set);
        $this->assertEquals($get, $this->builder->getLanguage());
    }

    public function testGetComment()
    {
        $this->assertNull($this->builder->getComment());
        $this->builder->setComment('Very good order!');
        $this->assertEquals('Very good order!', $this->builder->getComment());
    }

    public function testGetFirstName()
    {
        $this->assertNull($this->builder->getFirstName());
        $this->builder->setFirstName('Alexander');
        $this->assertEquals('Alexander', $this->builder->getFirstName());
    }

    public function testGetLastName()
    {
        $this->assertNull($this->builder->getLastName());
        $this->builder->setLastName('Ivanov');
        $this->assertEquals('Ivanov', $this->builder->getLastName());
    }

    public function testGetMiddleName()
    {
        $this->assertNull($this->builder->getMiddleName());
        $this->builder->setMiddleName('Ivanov');
        $this->assertEquals('Ivanov', $this->builder->getMiddleName());
    }

    public function testGetPhone()
    {
        $this->assertNull($this->builder->getPhone());
        $this->builder->setPhone('+79509999999');
        $this->assertEquals('+79509999999', $this->builder->getPhone());
    }

    public function testGetAddress()
    {
        $this->assertNull($this->builder->getAddress());
        $this->builder->setAddress('c.Патруши Сысертский р-н, Свердловская обл.');
        $this->assertEquals('c.Патруши Сысертский р-н, Свердловская обл.', $this->builder->getAddress());
    }

    public function testGetCountry()
    {
        $this->assertNull($this->builder->getCountry());
        $this->builder->setCountry('Россия');
        $this->assertEquals('Россия', $this->builder->getCountry());
    }

    public function testGetState()
    {
        $this->assertNull($this->builder->getCountry());
        $this->builder->setCountry('3422');
        $this->assertEquals('3422', $this->builder->getCountry());
    }

    public function testGetCity()
    {
        $this->assertNull($this->builder->getCity());
        $this->builder->setCity('Москва');
        $this->assertEquals('Москва', $this->builder->getCity());
    }

    public function testGetZip()
    {
        $this->assertNull($this->builder->getZip());
        $this->builder->setZip('D-80331 München');
        $this->assertEquals('D-80331 München', $this->builder->getZip());
        $this->builder->setZip(624003);
        $this->assertEquals('624003', $this->builder->getZip());
    }

    public function testGetPhoneVerified()
    {
        $this->assertNull($this->builder->getPhoneVerified());
        $this->builder->setPhoneVerified('+79509999999');
        $this->assertEquals('+79509999999', $this->builder->getPhoneVerified());
    }

    public function testGetDestPhoneNum()
    {
        $this->assertNull($this->builder->getDestPhoneNum());
        $this->builder->setDestPhoneNum('+79509999999');
        $this->assertEquals('+79509999999', $this->builder->getDestPhoneNum());
    }

    public function testGetMerchantOrderId()
    {
        $this->assertNull($this->builder->getMerchantOrderId());
        $this->builder->setMerchantOrderId('7950');
        $this->assertEquals('7950', $this->builder->getMerchantOrderId());
        $this->builder->setMerchantOrderId(7950);
        $this->assertEquals('7950', $this->builder->getMerchantOrderId());
    }

    public static function dataProviderGetPaymentTypeLimits(): array
    {
        return [
            [
                [
                    PaymentType::SBP      => [5000.3, 4000.7],
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
        $this->assertNull($this->builder->getPaymentTypeLimits());
        $this->builder->setPaymentTypeLimits($set);
        $this->assertEquals($get, $this->builder->getPaymentTypeLimits());
    }

    public function testGetBackUrl()
    {
        $this->assertNull($this->builder->getBackUrl());
        $this->builder->setBackUrl('https://google.com');
        $this->assertEquals('https://google.com', $this->builder->getBackUrl());
    }

    public function testGetDeepLink()
    {
        $this->assertNull($this->builder->getDeepLink());
        $this->builder->setDeepLink('https://google.com');
        $this->assertEquals('https://google.com', $this->builder->getDeepLink());
    }

    public function testGetEWallet()
    {
        $this->assertNull($this->builder->getEWallet());
        $this->builder->setEWallet('47416810600000000004');
        $this->assertEquals('47416810600000000004', $this->builder->getEWallet());
    }

    public function testToArrayIncludesSignature()
    {
        $this->builder->setShopId('shop')
            ->setOrderId('123')
            ->setSubtotalP(100)
            ->setPassword('secret')
            ->setUrlReturnOk('https://ok.url')
            ->setUrlReturnNo('https://no.url');

        $arr = $this->builder->toArray();
        $this->assertArrayHasKey('Signature', $arr);
        $this->assertEquals('mocked_signature', $arr['Signature']);
        $this->assertEquals('https://ok.url', $arr['URL_RETURN_OK']);
        $this->assertEquals('https://no.url', $arr['URL_RETURN_NO']);
    }

    public function testGetSignature()
    {
        $signature = $this->builder->setShopId('shop123')
            ->setOrderId('12345')
            ->setSubtotalP(100)
            ->setPassword('password')
            ->getSignature();
        $this->assertEquals('mocked_signature', $signature);
    }

    /**
     * @covers \Tmconsulting\Uniteller\Payment\PaymentBuilder::toArray
     *
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\NotValidParameterException
     * @throws \Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException
     */
    public function testNotSetNotExistToArray()
    {
        $this->builder->setShopId('009999')
            ->setOrderId(1)
            ->setSubtotalP(10)
            ->setUrlReturnOk('https://google.ru/?q=success')
            ->setUrlReturnNo('https://google.ru/?q=failure')
            ->setPassword('password');
        $arr = $this->builder->toArray();
        $this->assertArrayHasKey(UnitellerParameterName::SHOP_IDP, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::ORDER_IDP, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::SUBTOTAL_P, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::URL_RETURN_OK, $arr);
        $this->assertArrayHasKey(UnitellerParameterName::URL_RETURN_NO, $arr);

        $this->assertArrayNotHasKey(UnitellerParameterName::MEAN_TYPE, $this->builder->toArray());
        $this->builder->setMeanType(MeanType::VISA);
        $this->assertArrayHasKey(UnitellerParameterName::MEAN_TYPE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::E_MONEY_TYPE, $this->builder->toArray());
        $this->builder->setEMoneyType(EMoneyType::ANY);
        $this->assertArrayHasKey(UnitellerParameterName::E_MONEY_TYPE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::LIFETIME, $this->builder->toArray());
        $this->builder->setLifetime(100);
        $this->assertArrayHasKey(UnitellerParameterName::LIFETIME, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CUSTOMER_IDP, $this->builder->toArray());
        $this->builder->setCustomerIdp(1);
        $this->assertArrayHasKey(UnitellerParameterName::CUSTOMER_IDP, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CARD_IDP, $this->builder->toArray());
        $this->builder->setCardIdp('2200060300746821');
        $this->assertArrayHasKey(UnitellerParameterName::CARD_IDP, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::I_DATA, $this->builder->toArray());
        $this->builder->setIData('220006');
        $this->assertArrayHasKey(UnitellerParameterName::I_DATA, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::PT_CODE, $this->builder->toArray());
        $this->builder->setPtCode('dfs220');
        $this->assertArrayHasKey(UnitellerParameterName::PT_CODE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::ORDER_LIFETIME, $this->builder->toArray());
        $this->builder->setOrderLifetime(220);
        $this->assertArrayHasKey(UnitellerParameterName::ORDER_LIFETIME, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::MERCHANT_ORDER_ID, $this->builder->toArray());
        $this->builder->setMerchantOrderId(4567890);
        $this->assertArrayHasKey(UnitellerParameterName::MERCHANT_ORDER_ID, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::PAYMENT_TYPE_LIMITS, $this->builder->toArray());
        $this->builder->setPaymentTypeLimits('{"13":[5000.3,4000.7],"14":[12300.37]}');
        $this->assertArrayHasKey(UnitellerParameterName::PAYMENT_TYPE_LIMITS, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CALLBACK_FIELDS, $this->builder->toArray());
        $this->builder->setCallbackFields([CallbackFields::CARD_IDP]);
        $this->assertArrayHasKey(UnitellerParameterName::CALLBACK_FIELDS, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CURRENCY, $this->builder->toArray());
        $this->builder->setCurrency(Currency::RUB);
        $this->assertArrayHasKey(UnitellerParameterName::CURRENCY, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::EMAIL, $this->builder->toArray());
        $this->builder->setEmail('example@gmail.com');
        $this->assertArrayHasKey(UnitellerParameterName::EMAIL, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::BILL_LIFETIME, $this->builder->toArray());
        $this->builder->setBillLifetime(120);
        $this->assertArrayHasKey(UnitellerParameterName::BILL_LIFETIME, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CALLBACK_FORMAT, $this->builder->toArray());
        $this->builder->setCallbackFormat('json');
        $this->assertArrayHasKey(UnitellerParameterName::CALLBACK_FORMAT, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::BACK_URL, $this->builder->toArray());
        $this->builder->setBackUrl('https://google.ru/?q=success');
        $this->assertArrayHasKey(UnitellerParameterName::BACK_URL, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::DEEP_LINK, $this->builder->toArray());
        $this->builder->setDeepLink('https://google.ru/?q=success');
        $this->assertArrayHasKey(UnitellerParameterName::DEEP_LINK, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::LANGUAGE, $this->builder->toArray());
        $this->builder->setLanguage(Language::RU);
        $this->assertArrayHasKey(UnitellerParameterName::LANGUAGE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::E_WALLET, $this->builder->toArray());
        $this->builder->setEWallet('4325234');
        $this->assertArrayHasKey(UnitellerParameterName::E_WALLET, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::DEST_PHONE_NUM, $this->builder->toArray());
        $this->builder->setDestPhoneNum('+79120900000');
        $this->assertArrayHasKey(UnitellerParameterName::DEST_PHONE_NUM, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::COMMENT, $this->builder->toArray());
        $this->builder->setComment('My comment!');
        $this->assertArrayHasKey(UnitellerParameterName::COMMENT, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::FIRST_NAME, $this->builder->toArray());
        $this->builder->setFirstName('Alexander');
        $this->assertArrayHasKey(UnitellerParameterName::FIRST_NAME, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::LAST_NAME, $this->builder->toArray());
        $this->builder->setLastName('Ivanov');
        $this->assertArrayHasKey(UnitellerParameterName::LAST_NAME, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::MIDDLE_NAME, $this->builder->toArray());
        $this->builder->setMiddleName('Patronymic');
        $this->assertArrayHasKey(UnitellerParameterName::MIDDLE_NAME, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::PHONE, $this->builder->toArray());
        $this->builder->setPhone('+79120900000');
        $this->assertArrayHasKey(UnitellerParameterName::PHONE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::PHONE_VERIFIED, $this->builder->toArray());
        $this->builder->setPhoneVerified('+79501234567890');
        $this->assertArrayHasKey(UnitellerParameterName::PHONE_VERIFIED, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::ADDRESS, $this->builder->toArray());
        $this->builder->setAddress('some address');
        $this->assertArrayHasKey(UnitellerParameterName::ADDRESS, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::COUNTRY, $this->builder->toArray());
        $this->builder->setCountry('Argentina');
        $this->assertArrayHasKey(UnitellerParameterName::COUNTRY, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::STATE, $this->builder->toArray());
        $this->builder->setState('Argentina');
        $this->assertArrayHasKey(UnitellerParameterName::STATE, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::CITY, $this->builder->toArray());
        $this->builder->setCity('Moscow');
        $this->assertArrayHasKey(UnitellerParameterName::CITY, $this->builder->toArray());

        $this->assertArrayNotHasKey(UnitellerParameterName::ZIP, $this->builder->toArray());
        $this->builder->setZip(213123);
        $this->assertArrayHasKey(UnitellerParameterName::ZIP, $this->builder->toArray());
    }

    public function testGetRequestName()
    {
        $this->assertEquals(ApiEndpoints::PAYMENT, $this->builder->getEndpoint());
    }

    public function testProcess()
    {
        $this->builder->setBaseUri('https://example.com')
            ->setShopId('shop')
            ->setOrderId('123')
            ->setSubtotalP(100)
            ->setPassword('password')
            ->setUrlReturnOk('https://ok.url')
            ->setUrlReturnNo('https://no.url');

        $uri = $this->builder->process();
        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertEquals('https://example.com/pay?Shop_IDP=shop&Order_IDP=123&Subtotal_P=100&URL_RETURN_OK=https%3A%2F%2Fok.url&URL_RETURN_NO=https%3A%2F%2Fno.url&Signature=mocked_signature', $uri->getUri());
    }
}
