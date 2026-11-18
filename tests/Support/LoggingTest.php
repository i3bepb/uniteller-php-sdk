<?php

namespace Tmconsulting\Uniteller\Tests\Support;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\AbstractLogger;
use Tmconsulting\Uniteller\Parameter\Enum\SFields;
use Tmconsulting\Uniteller\Parameter\Enum\UnitellerParameterName;
use Tmconsulting\Uniteller\Request\RequestManager;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Support\LoggingPolicy;
use Tmconsulting\Uniteller\Support\LogSanitizer;
use Tmconsulting\Uniteller\Uniteller;

class LoggingTest extends TestCase
{
    public function testPolicyAndRecursiveSanitizerHideReceiptsWithoutChangingOriginalData(): void
    {
        $policy = new LoggingPolicy();
        $this->assertTrue($policy->shouldMaskPersonalData());
        $input = [
            'orders' => [['Order_ID' => '00123', 'Email' => 'buyer@example.test', 'Phone' => '+79991234567']],
            'parameters' => ['Password' => 'secret'],
            'hashes' => ['Password' => md5('secret')],
            'Receipt' => ['customer' => 'receipt-buyer', 'email' => 'receipt@example.test'],
        ];
        $original = $input;
        $masked = LogSanitizer::sanitize($input, $policy);
        $this->assertSame('00123', $masked['orders'][0]['Order_ID']);
        $this->assertSame('[REDACTED]', $masked['orders'][0]['Email']);
        $this->assertSame('[REDACTED]', $masked['orders'][0]['Phone']);
        $this->assertSame('[REDACTED]', $masked['parameters']['Password']);
        $this->assertSame('[REDACTED]', $masked['hashes']['Password']);
        $this->assertSame('[REDACTED]', $masked['Receipt']);
        $this->assertSame($original, $input);
        $this->assertSame($policy, $policy->maskPersonalData(false));
        $visible = LogSanitizer::sanitize($input, $policy);
        $this->assertSame($input['orders'], $visible['orders']);
        $this->assertSame('[REDACTED]', $visible['parameters']['Password']);
        $this->assertSame('[REDACTED]', $visible['hashes']['Password']);
        $this->assertSame($input['Receipt'], $visible['Receipt']);
    }

    public function testPaymentFormReceiptContextRespectsPolicy(): void
    {
        $receipt = base64_encode('payment-form-receipt');
        foreach ([true, false] as $mask) {
            $input = ['uniteller' => ['receipt' => $receipt]];
            $safe = LogSanitizer::sanitize($input, (new LoggingPolicy())->maskPersonalData($mask));
            $this->assertSame($mask ? '[REDACTED]' : $receipt, $safe['uniteller']['receipt']);
            $this->assertSame($receipt, $input['uniteller']['receipt']);
        }
    }

    public function testSanitizerUsesExactFieldNames(): void
    {
        $receipt = base64_encode('receipt-payload');
        $input = [
            UnitellerParameterName::EMAIL => 'buyer@example.test',
            UnitellerParameterName::PHONE_VERIFIED => '+79991234567',
            UnitellerParameterName::CUSTOMER_IDP => 'customer-123',
            UnitellerParameterName::PASSWORD => 'secret',
            SFields::BOOKINGCOM_PINCODE => 'booking-pin',
            UnitellerParameterName::RECEIPT => $receipt,
            'email' => 'unchanged-email',
            'E_mail' => 'unchanged-separated-email',
            'Phone-Verified' => 'unchanged-phone',
            'CustomerIDP' => 'unchanged-customer',
            'PASSWORD' => 'unchanged-password',
            'bookingcompincode' => 'unchanged-pin',
            'receipt' => $receipt,
        ];

        $safe = LogSanitizer::sanitize($input, new LoggingPolicy());

        foreach ([UnitellerParameterName::EMAIL, UnitellerParameterName::PHONE_VERIFIED,
            UnitellerParameterName::CUSTOMER_IDP, UnitellerParameterName::PASSWORD,
            SFields::BOOKINGCOM_PINCODE, UnitellerParameterName::RECEIPT] as $field) {
            $this->assertSame('[REDACTED]', $safe[$field]);
        }
        foreach (['email', 'E_mail', 'Phone-Verified', 'CustomerIDP', 'PASSWORD', 'bookingcompincode', 'receipt'] as $field) {
            $this->assertSame($input[$field], $safe[$field]);
        }
    }

    public function testXmlUsesExactElementAndAttributeNames(): void
    {
        $body = '<order Email="buyer@example.test" email="unchanged-email" Customer_IDP="customer-123" CustomerIDP="unchanged-customer">'
            . '<Password>secret</Password><password>unchanged-password</password>'
            . '<var name="PhoneVerified"><string>+79991234567</string></var>'
            . '<var name="phoneverified"><string>unchanged-phone</string></var>'
            . '<Receipt>receipt-payload</Receipt><receipt>unchanged-receipt</receipt></order>';

        $safe = LogSanitizer::sanitizeBody($body, new LoggingPolicy(), 'application/xml');

        $this->assertStringContainsString('Email="[REDACTED]"', $safe);
        $this->assertStringContainsString('email="unchanged-email"', $safe);
        $this->assertStringContainsString('Customer_IDP="[REDACTED]"', $safe);
        $this->assertStringContainsString('CustomerIDP="unchanged-customer"', $safe);
        $this->assertStringContainsString('<Password>[REDACTED]</Password>', $safe);
        $this->assertStringContainsString('<password>unchanged-password</password>', $safe);
        $this->assertStringNotContainsString('+79991234567', $safe);
        $this->assertStringContainsString('unchanged-phone', $safe);
        $this->assertStringContainsString('<Receipt>[REDACTED]</Receipt>', $safe);
        $this->assertStringContainsString('<receipt>unchanged-receipt</receipt>', $safe);
    }

    public function testReturnUrlAndRawPostUseExactContextKeys(): void
    {
        $query = http_build_query(['Email' => 'buyer@example.test', 'Password' => 'secret']);
        $input = [
            'headers' => ['Content-Type' => ['application/x-www-form-urlencoded']],
            UnitellerParameterName::URL_RETURN => 'https://merchant.test/callback?' . $query,
            'raw_post' => $query,
        ];

        $safe = LogSanitizer::sanitize($input, new LoggingPolicy());
        parse_str(parse_url($safe[UnitellerParameterName::URL_RETURN], PHP_URL_QUERY), $urlParameters);
        parse_str($safe['raw_post'], $postParameters);

        $this->assertSame(['Email' => '[REDACTED]', 'Password' => '[REDACTED]'], $urlParameters);
        $this->assertSame($urlParameters, $postParameters);
        $this->assertSame($query, $input['raw_post']);
    }

    public function testSanitizeRejectsNullLoggingPolicy(): void
    {
        $this->expectException(\TypeError::class);

        call_user_func([LogSanitizer::class, 'sanitize'], [], null);
    }

    /** @dataProvider bodies */
    public function testBodiesKeepBusinessDataAndAlwaysHidePasswords(string $body, string $type): void
    {
        foreach ([true, false] as $mask) {
            $policy = (new LoggingPolicy())->maskPersonalData($mask);
            $logged = LogSanitizer::sanitizeBody($body, $policy, $type);
            $this->assertStringContainsString('00123', $logged);
            $this->assertStringNotContainsString('secret-9D', $logged);
            if ($mask) {
                $this->assertStringNotContainsString('buyer@example.test', urldecode($logged));
                $this->assertStringNotContainsString('79991234567', urldecode($logged));
            } else {
                $this->assertStringContainsString('buyer@example.test', urldecode($logged));
                $this->assertStringContainsString('79991234567', urldecode($logged));
            }
        }
    }

    public function bodies(): array
    {
        return [
            ['{"orders":[{"id":"00123","Email":"buyer@example.test","Phone":"+79991234567","Password":"secret-9D"}]}', 'application/json'],
            ['<orders><order id="00123" Email="buyer@example.test"><Phone>+79991234567</Phone><Password>secret-9D</Password></order></orders>', 'application/xml'],
            ['<data><var name="Order_ID"><string>00123</string></var><var name="Email"><string>buyer@example.test</string></var><var name="Phone"><string>+79991234567</string></var><var name="Password"><string>secret-9D</string></var></data>', 'application/xml'],
            ["Order_ID;Email;Phone;Password\n00123;buyer@example.test;+79991234567;secret-9D", 'text/csv'],
            ['Order_ID=00123&Email=buyer%40example.test&Phone=%2B79991234567&Password=secret-9D', 'application/x-www-form-urlencoded'],
        ];
    }

    public function testOnlyExplicitBodiesAndUrlsAreParsed(): void
    {
        $input = [
            'body' => '{"Email":"buyer@example.test","Password":"secret"}',
            'data' => '{"Email":"buyer@example.test","Password":"secret"}',
            'xml_data' => '<Password>secret</Password>',
            'diagnostic' => '[diagnostic] ordinary text',
            'url' => 'https://user:secret@merchant.test/callback?Order_ID=00123&Password=secret&Email=buyer%40example.test',
            'headers' => ['Content-Type' => 'application/json'],
        ];
        $masked = LogSanitizer::sanitize($input, new LoggingPolicy());
        foreach (['body', 'url'] as $field) {
            $this->assertStringNotContainsString('secret', $masked[$field]);
            $this->assertStringNotContainsString('buyer', $masked[$field]);
        }
        foreach (['data', 'xml_data', 'diagnostic'] as $field) {
            $this->assertSame($input[$field], $masked[$field]);
        }
        $this->assertSame(['Content-Type' => 'application/json'], $masked['headers']);
        $this->assertStringContainsString('Order_ID=00123', $masked['url']);
        $visible = LogSanitizer::sanitize($input, (new LoggingPolicy())->maskPersonalData(false));
        $this->assertStringContainsString('buyer', $visible['body']);
        $this->assertStringNotContainsString('secret', $visible['body']);
        $this->assertSame($input['headers'], $visible['headers']);
    }

    public function testMalformedAndUnknownBodiesAreNeverLoggedRaw(): void
    {
        $policy = new LoggingPolicy();
        foreach (['{"Password":"secret"', '<Password>secret', 'email: buyer@example.test', "\nsecret"] as $body) {
            $this->assertSame('[unparsed body: ' . strlen($body) . ' bytes]', LogSanitizer::sanitizeBody($body, $policy));
        }
        $this->assertSame('  ', LogSanitizer::sanitizeBody('  ', $policy));
        $deep = 'data' . str_repeat('[nested]', 100) . '=secret';
        $this->assertStringNotContainsString('secret', LogSanitizer::sanitizeBody($deep, $policy, 'application/x-www-form-urlencoded'));
    }

    public function testCompleteReceiptsFollowPersonalDataPolicyWithoutDecoding(): void
    {
        $receipt = ['unknown_future_field' => ['secret-receipt-content']];
        $encoded = base64_encode(json_encode($receipt));
        $object = new class implements \JsonSerializable {
            public function jsonSerialize(): array
            {
                throw new \LogicException('Receipt must never be serialized for logging');
            }
        };
        $data = ['Order_ID' => '00123', 'Receipt' => $encoded, 'nested' => ['Receipts' => [$receipt]],
            'hashes' => ['Receipt' => hash('sha256', $encoded)], 'object' => ['Receipt' => $object]];
        foreach ([true, false] as $mask) {
            $safe = LogSanitizer::sanitize($data, (new LoggingPolicy())->maskPersonalData($mask));
            $this->assertSame('00123', $safe['Order_ID']);
            $this->assertSame($mask ? '[REDACTED]' : $encoded, $safe['Receipt']);
            $this->assertSame($mask ? '[REDACTED]' : [$receipt], $safe['nested']['Receipts']);
            $this->assertSame($mask ? '[REDACTED]' : hash('sha256', $encoded), $safe['hashes']['Receipt']);
            $this->assertSame($mask ? '[REDACTED]' : $object, $safe['object']['Receipt']);
            $this->assertSame($encoded, $data['Receipt']);
            $this->assertSame($object, $data['object']['Receipt']);
        }
    }

    /** @dataProvider receiptBodies */
    public function testReceiptBodiesAreMaskedAsAWholeOrPreserved(string $body, string $type, string $receipt): void
    {
        foreach ([true, false] as $mask) {
            $safe = LogSanitizer::sanitizeBody($body, (new LoggingPolicy())->maskPersonalData($mask), $type);
            $this->assertStringContainsString('00123', $safe);
            if ($mask) {
                $this->assertStringContainsString('[REDACTED]', urldecode($safe));
                $this->assertStringNotContainsString('receipt-payload', $safe);
                $this->assertStringNotContainsString('futureField', $safe);
                $this->assertStringNotContainsString(base64_encode('receipt-payload'), $safe);
            } else {
                $this->assertStringContainsString($receipt, urldecode($safe));
            }
        }
    }

    public function receiptBodies(): array
    {
        $encoded = base64_encode('receipt-payload');
        return [
            ['{"Order_ID":"00123","Receipt":{"futureField":"receipt-payload"}}', 'application/json', 'receipt-payload'],
            ['{"Order_ID":"00123","Receipts":[{"futureField":"receipt-payload"}]}', 'application/json', 'receipt-payload'],
            ['{"Order_ID":"00123","Receipt":"' . $encoded . '"}', 'application/json', $encoded],
            ['{"Order_ID":"00123","Receipt":"<Password>receipt-payload</Password>"}', 'application/json', '<Password>receipt-payload</Password>'],
            ['<order id="00123"><Receipt futureField="receipt-payload"><Password>receipt-payload</Password></Receipt></order>', 'application/xml', 'receipt-payload'],
            ['<order id="00123" Receipt="' . $encoded . '"/>', 'application/xml', $encoded],
            ['<order id="00123"><Receipt>' . $encoded . '</Receipt></order>', 'application/xml', $encoded],
            ['<data><var name="Order_ID"><string>00123</string></var><var name="Receipt"><string>' . $encoded . '</string></var></data>', 'application/xml', $encoded],
            ["Order_ID;Receipt\n00123;" . $encoded, 'text/csv', $encoded],
            ['Order_ID=00123&Receipt=' . urlencode($encoded), 'application/x-www-form-urlencoded', $encoded],
            ['Order_ID=00123&Receipt%5BfutureField%5D=receipt-payload', 'application/x-www-form-urlencoded', 'receipt-payload'],
        ];
    }

    public function testSignatureCalculationDebugLogsAllValuesRegardlessOfPersonalDataPolicy(): void
    {
        foreach ([true, false] as $mask) {
            foreach (['createMd5', 'createSha256'] as $method) {
                $logger = new RecordingLogger();
                $signature = new Signature();
                $signature->setLogger($logger);
                $signature->setLoggingPolicy((new LoggingPolicy())->maskPersonalData($mask));
                $signature->setDebug(true);
                $receipt = base64_encode('signature-receipt-payload');
                $parameters = [
                    UnitellerParameterName::PASSWORD => 'secret-9D',
                    UnitellerParameterName::SHOP_IDP => 'shop123',
                    UnitellerParameterName::EMAIL => 'buyer@example.test',
                    UnitellerParameterName::PHONE_VERIFIED => '+79991234567',
                    UnitellerParameterName::CUSTOMER_IDP => 'customer-123',
                    UnitellerParameterName::I_DATA => 'passenger-data',
                    UnitellerParameterName::RECEIPT => $receipt,
                    SFields::BOOKINGCOM_PINCODE => 'booking-pin',
                    'PasswordHint' => 'reference',
                    'CustomerEmail' => 'undocumented-alias',
                ];
                $actual = $signature->setParameters($parameters)->{$method}();
                $algorithm = $method === 'createMd5' ? 'md5' : 'sha256';
                $expected = strtoupper(hash($algorithm, implode('&', array_map(static function ($value) use ($algorithm) {
                    return hash($algorithm, $value);
                }, $parameters))));
                $this->assertSame($expected, $actual);
                $record = $logger->records[0]['context']['uniteller'];
                $this->assertSame($parameters, $record['parameters']);
                foreach ($parameters as $field => $value) {
                    $this->assertSame(hash($algorithm, $value), $record['hashes'][$field]);
                }
                $this->assertSame(array_keys($parameters), array_keys($record['hashes']));
                $this->assertSame($actual, $record['signature']);
                $this->assertSame($actual, strtoupper(hash($algorithm, implode('&', $record['hashes']))));
            }
        }
    }

    public function testFacadeLogsRequestParametersAndResponseBodyWithoutDebugAndIsolatesPolicies(): void
    {
        foreach ([false, true] as $mask) {
            $logger = new RecordingLogger();
            $receipt = base64_encode('response-receipt-payload');
            $responseBody = "Order_ID;Email;Receipt\n00123;buyer@example.test;" . $receipt;
            $client = $this->createMock(ClientInterface::class);
            $client->expects($this->once())->method('sendRequest')->willReturnCallback(function ($request) use ($responseBody) {
                $this->assertSame('', $request->getUri()->getQuery());
                $this->assertSame(0, $request->getBody()->tell());
                $this->assertStringContainsString('Password=secret-9D', (string) $request->getBody());
                return new Response(200, ['Content-Type' => 'text/csv', 'Set-Cookie' => 'session=token'], $responseBody);
            });
            $sdk = new Uniteller();
            $policy = (new LoggingPolicy())->maskPersonalData($mask);
            $this->assertSame($sdk, $sdk->withLoggingPolicy($policy));
            $sdk->withLogger($logger)->withHttpTransport($client, new HttpFactory(), new HttpFactory());
            // Exercise the real container and manager while keeping business-response parsing out of this test.
            $builder = $sdk->results()->setShopId('shop')->setLogin('login')->setPassword('secret-9D')->setFormat('csv');
            $containerProperty = new \ReflectionProperty(Uniteller::class, 'container');
            $containerProperty->setAccessible(true);
            $container = $containerProperty->getValue($sdk);
            $decoded = $container->get(RequestManager::class)->executeRequest($builder);
            $this->assertSame($responseBody, (string) $decoded->getResponse()->getBody());
            $this->assertSame('buyer@example.test', $decoded->getData()[0]['Email']);
            $this->assertSame($receipt, $decoded->getData()[0]['Receipt']);
            $this->assertSame('session=token', $decoded->getResponse()->getHeaderLine('Set-Cookie'));
            $this->assertCount(2, $logger->records);
            $this->assertArrayNotHasKey('body', $logger->records[0]['context']['uniteller']);
            $this->assertSame(['uniteller'], array_keys($logger->records[0]['context']));
            $this->assertSame(['uniteller'], array_keys($logger->records[1]['context']));
            $this->assertSame('uniteller.http.request_sending', $logger->records[0]['message']);
            $this->assertSame('uniteller.http.response_received', $logger->records[1]['message']);
            $this->assertSame('[REDACTED]', $logger->records[0]['context']['uniteller']['parameters']['Password']);
            $this->assertSame(['Content-Type' => 'application/x-www-form-urlencoded'], $logger->records[0]['context']['uniteller']['headers']);
            $responseLog = $logger->records[1]['context']['uniteller'];
            $this->assertStringContainsString($mask ? '[REDACTED]' : 'buyer@example.test', $responseLog['body']);
            $this->assertSame(['Content-Type' => 'text/csv'], $responseLog['headers']);
            if ($mask) {
                $this->assertStringNotContainsString($receipt, $responseLog['body']);
            } else {
                $this->assertStringContainsString($receipt, $responseLog['body']);
            }
            $this->assertStringNotContainsString('secret-9D', json_encode($logger->records));
        }
    }

}

class RecordingLogger extends AbstractLogger
{
    public $records = [];

    public function log($level, $message, array $context = []): void
    {
        $this->records[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }
}
