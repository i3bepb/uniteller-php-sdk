<?php

namespace Tmconsulting\Uniteller\Tests\Request;

use I3bepb\ReflectionForTest\AccessToMethod;
use Psr\Log\LoggerInterface;
use Tmconsulting\Uniteller\Callback\Callback;
use Tmconsulting\Uniteller\Dependency\Container;
use Tmconsulting\Uniteller\Exception\Parameter\RequiredParameterException;
use Tmconsulting\Uniteller\Exception\UnitellerRuntimeException;
use Tmconsulting\Uniteller\Order\CallbackOrder;
use Tmconsulting\Uniteller\Order\Status;
use Tmconsulting\Uniteller\Parameter\CallbackFieldsParameter;
use Tmconsulting\Uniteller\Receipt\FiscalReceipt;
use Tmconsulting\Uniteller\Request\ParserReceiptFromBase64;
use Tmconsulting\Uniteller\Signature\Signature;
use Tmconsulting\Uniteller\Tests\TestCase;
use Tmconsulting\Uniteller\Uniteller;

/**
 * @coversDefaultClass \Tmconsulting\Uniteller\Callback\Callback
 */
class CallbackTest extends TestCase
{
    use AccessToMethod;

    private const PASSWORD = 'secret';

    /** @var Callback */
    private $callback;

    /** @var Container */
    private $container;

    private $originalPost;
    private $originalGet;
    private $originalServer;

    protected function setUp(): void
    {
        $this->originalPost = $_POST;
        $this->originalGet = $_GET;
        $this->originalServer = $_SERVER;
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->container = new Container();
        $this->callback = $this->container->get(Callback::class);
        $this->callback->setPassword(self::PASSWORD);
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;
        $_GET = $this->originalGet;
        $_SERVER = $this->originalServer;
    }

    public function testSetAndGetPassword()
    {
        $this->assertSame($this->callback, $this->callback->setPassword('another-secret'));
        $this->assertSame('another-secret', $this->callback->getPassword());
    }

    public function testGetPasswordThrowsException()
    {
        $callback = $this->container->get(Callback::class);
        $this->expectException(RequiredParameterException::class);
        $callback->getPassword();
    }

    public function testProcessWithMissingPostParams()
    {
        $this->expectCallbackError('Not found parameters', 400);
        $this->callback->process();
    }

    public function testProcessRejectsNonPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectCallbackError('Method Not Allowed', 405);
        $this->callback->process();
    }

    public function testExitWithMessage()
    {
        $this->expectCallbackError('Test error', 400);
        $this->privateMethodWithParameters($this->callback, 'exitWithMessage', ['Test error', 400]);
    }

    /**
     * @dataProvider requestLoggingProvider
     */
    public function testRejectedRequestIsLoggedBeforeValidation(array $server, string $url, string $ip, string $userAgent)
    {
        foreach (['HTTPS', 'HTTP_HOST', 'REQUEST_URI', 'REMOTE_ADDR', 'HTTP_USER_AGENT'] as $key) {
            unset($_SERVER[$key]);
        }
        $_SERVER = array_replace($_SERVER, $server, ['REQUEST_METHOD' => 'GET']);
        $_POST = ['Order_ID' => 'unverified-order'];
        $_GET = ['source' => 'uniteller'];
        $logged = false;
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with(
            'Request from Uniteller: ' . $url,
            $this->callback(function (array $context) use (&$logged, $ip, $userAgent) {
                $this->assertSame($_POST, $context['post_data']);
                $this->assertSame($_GET, $context['get_query']);
                $this->assertSame($ip, $context['ip']);
                $this->assertSame($userAgent, $context['user_agent']);
                // В CLI тело php://input пустое; ключ должен присутствовать и в таком случае.
                $this->assertSame('', $context['raw_post']);
                $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $context['datetime']);
                $logged = true;

                return true;
            })
        );
        $logger->expects($this->once())->method('error')->with('Method Not Allowed')
            ->willReturnCallback(function () use (&$logged) {
                $this->assertTrue($logged, 'The incoming request must be logged before validation fails.');
            });
        $this->callback->setLogger($logger);

        $this->expectCallbackError('Method Not Allowed', 405);
        $this->callback->process();
    }

    public function requestLoggingProvider(): array
    {
        $server = [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'merchant.test',
            'REQUEST_URI' => '/webhook?source=uniteller',
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_USER_AGENT' => 'Uniteller test callback',
        ];

        return [
            'https' => [$server, 'https://merchant.test/webhook?source=uniteller', '192.0.2.10', 'Uniteller test callback'],
            'http' => [array_replace($server, ['HTTPS' => 'off']), 'http://merchant.test/webhook?source=uniteller', '192.0.2.10', 'Uniteller test callback'],
            'defaults' => [[], 'http://localhost', '?.?.?.?', 'Unknown'],
        ];
    }

    /**
     * @dataProvider invalidRequestProvider
     */
    public function testValidationStopsBeforePasswordAndSignature(array $post, string $method, string $message, int $code)
    {
        $_POST = $post;
        $_SERVER['REQUEST_METHOD'] = $method;
        $signature = $this->createMock(Signature::class);
        $signature->expects($this->never())->method('setParameters');
        $signature->expects($this->never())->method('verify');
        $callback = $this->getMockBuilder(Callback::class)
            ->setConstructorArgs([$signature])
            ->onlyMethods(['getPassword'])
            ->getMock();
        $callback->expects($this->never())->method('getPassword');
        $callback->setLogger($this->createMock(LoggerInterface::class));
        $callback->setContainer($this->container);
        $this->expectParserNotCalled();

        $this->expectCallbackError($message, $code);
        $callback->process();
    }

    public function invalidRequestProvider(): array
    {
        $post = ['Order_ID' => '12345', 'Status' => 'paid', 'Signature' => 'unverified'];
        $fiscal = $post + ['Receipt' => '', 'ReceiptSignature' => 'unverified'];
        $cases = [
            'method before parameters' => [[], 'GET', 'Method Not Allowed', 405],
            'receipt without signature' => [$post + ['Receipt' => ''], 'POST', 'Receipt and ReceiptSignature must be provided together', 400],
            'signature without receipt' => [$post + ['ReceiptSignature' => 'unverified'], 'POST', 'Receipt and ReceiptSignature must be provided together', 400],
            'null receipt' => [array_replace($fiscal, ['Receipt' => null]), 'POST', 'Invalid callback parameter: Receipt', 400],
            'array receipt signature' => [array_replace($fiscal, ['ReceiptSignature' => ['unexpected']]), 'POST', 'Invalid callback parameter: ReceiptSignature', 400],
            'ordinary numeric total' => [$post + ['Total' => 50], 'POST', 'Invalid callback parameter: Total', 400],
            'fiscal array total' => [$fiscal + ['Total' => ['50.00']], 'POST', 'Invalid callback parameter: Total', 400],
        ];
        foreach (['Order_ID', 'Status', 'Signature'] as $field) {
            $missing = $post;
            unset($missing[$field]);
            $cases['missing ' . $field] = [$missing, 'POST', 'Not found parameters', 400];
            $cases['empty ' . $field] = [array_replace($post, [$field => '']), 'POST', 'Not found parameters', 400];
            $cases['array ' . $field] = [array_replace($post, [$field => ['unexpected']]), 'POST', 'Invalid callback parameter: ' . $field, 400];
        }

        return $cases;
    }

    public function testMissingPasswordStopsBeforeSignatureAndReceiptParsing()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $signature = $this->createMock(Signature::class);
        $signature->expects($this->never())->method('setParameters');
        $signature->expects($this->never())->method('verify');
        $callback = new Callback($signature);
        $callback->setLogger($this->createMock(LoggerInterface::class));
        $callback->setContainer($this->container);
        $this->expectParserNotCalled();

        $this->expectException(RequiredParameterException::class);
        $callback->process();
    }

    public function testReceiptParserFailureIsLoggedAndConvertedToCallbackError()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $exception = new \RuntimeException('Invalid fiscal data');
        $parser = $this->createMock(ParserReceiptFromBase64::class);
        $parser->expects($this->once())->method('parse')->with($_POST['Receipt'])->willThrowException($exception);
        $this->container->set(ParserReceiptFromBase64::class, $parser);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))->method('error')->withConsecutive(
            ['Receipt parsing failed', ['exception' => $exception]],
            ['Invalid receipt']
        );
        $this->callback->setLogger($logger);

        $this->expectCallbackError('Invalid receipt', 400);
        $this->callback->process();
    }

    public function testFiscalOrderPreservesAllFieldsAndDoesNotMutatePost()
    {
        $_POST = [
            'Order_ID' => '00012345', 'Status' => 'canceled',
            'PaymentType' => '01', 'Total' => '100.50', 'Balance' => '0.00',
            'BillNumber' => '000123', 'ApprovalCode' => 'approved', 'AcquirerID' => 'bank',
            'Card_IDP' => 'card', 'CardNumber' => '411111******1111',
            'Customer_IDP' => 'customer', 'ECI' => '7', 'EMoneyType' => 'wallet',
            'Signature' => strtoupper(md5(
                '00012345' . 'canceled' . '01' . '100.50' . '0.00' . '000123' . 'approved' . 'bank'
                . 'card' . '411111******1111' . 'customer' . '7' . 'wallet' . self::PASSWORD
            )),
            'Receipt' => $this->encodedReceipts(),
        ];
        $_POST['ReceiptSignature'] = strtoupper(hash('sha256', '00012345' . 'canceled' . $_POST['Receipt'] . self::PASSWORD));
        $post = $_POST;

        $order = $this->callback->process();

        $this->assertSame('00012345', $order->getOrderId());
        $this->assertSame(Status::CANCELLED, $order->getStatus());
        $this->assertSame('bank', $order->getAcquirerId());
        $this->assertSame('approved', $order->getApprovalCode());
        $this->assertSame('000123', $order->getBillNumber());
        $this->assertSame('card', $order->getCardId());
        $this->assertSame('411111******1111', $order->getCardNumber());
        $this->assertSame('customer', $order->getCustomerId());
        $this->assertSame('7', $order->getEci());
        $this->assertSame('wallet', $order->getEMoneyType());
        $this->assertSame(1, $order->getPaymentType());
        $this->assertSame('100.50', $order->getTotal());
        $this->assertSame('0.00', $order->getBalance());
        $this->assertSame($post['Signature'], $order->getSignature());
        $this->assertSame($post['ReceiptSignature'], $order->getReceiptSignature());
        $this->assertCount(1, $order->getReceipts());
        $this->assertSame($post, $_POST);
    }

    public function testSameCallbackCanProcessFiscalThenOrdinaryRequest()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $this->assertTrue($this->callback->process()->hasReceipts());

        $_POST = $this->ordinaryPost('canceled');
        $this->expectParserNotCalled();
        $order = $this->callback->process();

        $this->assertSame(Status::CANCELLED, $order->getStatus());
        $this->assertSame($_POST['Signature'], $order->getSignature());
        $this->assertSame([], $order->getReceipts());
        $this->assertNull($order->getReceiptSignature());
    }

    public function testOrdinaryCallbackThroughPublicApi()
    {
        $_POST = $this->ordinaryPost();

        $order = (new Uniteller())->callback()->setPassword(self::PASSWORD)->process();

        $this->assertInstanceOf(CallbackOrder::class, $order);
        $this->assertSame('12345', $order->getOrderId());
        $this->assertSame(Status::PAID, $order->getStatus());
        $this->assertSame($_POST['Signature'], $order->getSignature());
        $this->assertNull($order->getPaymentType());
        $this->assertFalse($order->hasReceipts());
        $this->assertSame([], $order->getReceipts());
        $this->assertNull($order->getReceiptSignature());
    }

    public function testOrdinaryCallbackDoesNotCallReceiptParser()
    {
        $_POST = $this->ordinaryPost();
        $this->expectParserNotCalled();

        $this->assertFalse($this->callback->process()->hasReceipts());
    }

    public function testFiscalCallback()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());

        $order = $this->callback->process();

        $this->assertInstanceOf(CallbackOrder::class, $order);
        $this->assertTrue($order->hasReceipts());
        $this->assertCount(1, $order->getReceipts());
        $this->assertInstanceOf(FiscalReceipt::class, $order->getReceipts()[0]);
        $this->assertSame($_POST['ReceiptSignature'], $order->getReceiptSignature());
        $this->assertSame($_POST['Signature'], $order->getSignature());
    }

    public function testMultipleReceipts()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts(2));

        $order = $this->callback->process();

        $this->assertCount(2, $order->getReceipts());
        foreach ($order->getReceipts() as $receipt) {
            $this->assertInstanceOf(FiscalReceipt::class, $receipt);
        }
    }

    public function testPreauthorizationWithoutReceiptUsesPostFieldOrder()
    {
        $this->expectParserNotCalled();
        $post = $this->preauthorizationPost();

        foreach ([$post, array_reverse($post, true)] as $parameters) {
            $_POST = $parameters;
            $signed = '';
            foreach ($parameters as $field => $value) {
                if (!in_array($field, ['Signature', 'Receipt', 'ReceiptSignature'], true)) {
                    $signed .= $value;
                }
            }
            $_POST['Signature'] = strtoupper(md5($signed . self::PASSWORD));
            $order = $this->callback->process();

            $this->assertSame('2609280002_18', $order->getOrderId());
            $this->assertSame(Status::AUTHORIZED, $order->getStatus());
            $this->assertSame('50.00', $order->getTotal());
            $this->assertSame([], $order->getReceipts());
            $this->assertFalse($order->hasReceipts());
            $this->assertSame($post['ReceiptSignature'], $order->getReceiptSignature());
        }
    }

    public function testCallbackVerificationLogPreservesFieldNamesAndMasksPassword()
    {
        $_POST = $this->preauthorizationPost();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug')->with(
            'Signature verify md5',
            $this->callback(function (array $context) {
                $this->assertSame([
                    'Order_ID' => '2609280002_18',
                    'Status' => 'authorized',
                    'ApprovalCode' => '6559F0',
                    'BillNumber' => '627118788812',
                    'Total' => '50.00',
                    'ECI' => '7',
                    'CardNumber' => '220006******6821',
                    'PaymentType' => '1',
                    'AcquirerID' => '35',
                    'Password' => '*****',
                ], $context['parameters']);
                $this->assertSame($_POST['Signature'], $context['expected_signature']);
                $this->assertSame($_POST['Signature'], $context['received_signature']);
                $this->assertTrue($context['valid']);

                return true;
            })
        );
        $container = new Container([LoggerInterface::class => $logger], true);
        $callback = $container->get(Callback::class)->setPassword(self::PASSWORD);

        $this->assertSame('2609280002_18', $callback->process()->getOrderId());
    }

    /**
     * @dataProvider tamperedPreauthorizationProvider
     */
    public function testPreauthorizationWithoutReceiptStillVerifiesSignatures(string $field, string $message)
    {
        $_POST = $this->preauthorizationPost();
        $_POST[$field] .= '0';
        $this->expectParserNotCalled();

        $this->expectCallbackError($message, 403);
        $this->callback->process();
    }

    public function tamperedPreauthorizationProvider(): array
    {
        return [
            ['Signature', 'Signature not valid'],
            ['Total', 'Signature not valid'],
            ['ECI', 'Signature not valid'],
            ['CardNumber', 'Signature not valid'],
            ['PaymentType', 'Signature not valid'],
            ['AcquirerID', 'Signature not valid'],
            ['Receipt', 'Receipt signature not valid'],
            ['ReceiptSignature', 'Receipt signature not valid'],
        ];
    }

    /**
     * @dataProvider missingReceiptFieldProvider
     */
    public function testReceiptFieldsMustBeProvidedTogether(string $missingField)
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        unset($_POST[$missingField]);
        $this->expectParserNotCalled();

        $this->expectCallbackError('Receipt and ReceiptSignature must be provided together', 400);
        $this->callback->process();
    }

    public function missingReceiptFieldProvider(): array
    {
        return [['Receipt'], ['ReceiptSignature']];
    }

    public function testInvalidReceiptSignatureIsRejectedBeforeParsing()
    {
        $_POST = $this->fiscalPost('invalid base64');
        $_POST['ReceiptSignature'] = str_repeat('0', 64);
        $this->expectParserNotCalled();

        $this->expectCallbackError('Receipt signature not valid', 403);
        $this->callback->process();
    }

    public function testInvalidMainSignatureWithValidReceiptSignature()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $_POST['Signature'] = 'invalid_signature';
        $this->expectParserNotCalled();

        $this->expectCallbackError('Signature not valid', 403);
        $this->callback->process();
    }

    public function testMainSignatureIsCheckedBeforeReceiptSignature()
    {
        $_POST = $this->fiscalPost('invalid base64');
        $_POST['Signature'] = 'invalid_signature';
        $_POST['ReceiptSignature'] = 'invalid_signature';
        $this->expectParserNotCalled();

        $this->expectCallbackError('Signature not valid', 403);
        $this->callback->process();
    }

    public function testOrdinaryCallbackWithInvalidSignature()
    {
        $_POST = $this->ordinaryPost();
        $_POST['Signature'] = 'invalid_signature';

        $this->expectCallbackError('Signature not valid', 403);
        $this->callback->process();
    }

    /**
     * @dataProvider invalidReceiptProvider
     */
    public function testInvalidReceiptIsReportedAsBadRequest(string $receipt)
    {
        $_POST = $this->fiscalPost($receipt);

        $this->expectCallbackError('Invalid receipt', 400);
        $this->callback->process();
    }

    public function invalidReceiptProvider(): array
    {
        return [
            'invalid base64' => ['not_a_base64_string'],
            'invalid json' => [base64_encode('{"invalid": json')],
            'scalar json' => [base64_encode('"invalid"')],
            'invalid receipt element' => [base64_encode('[1]')],
        ];
    }

    /**
     * @dataProvider rawStatusProvider
     */
    public function testSignaturesUseRawStatus(string $status, string $expectedStatus, bool $fiscal)
    {
        $_POST = $fiscal
            ? $this->fiscalPost($this->encodedReceipts(), $status)
            : $this->ordinaryPost($status);

        $this->assertSame($expectedStatus, $this->callback->process()->getStatus());
    }

    public function rawStatusProvider(): array
    {
        return [
            'ordinary canceled' => ['canceled', Status::CANCELLED, false],
            'fiscal canceled' => ['canceled', Status::CANCELLED, true],
            'ordinary Paid' => ['Paid', Status::PAID, false],
            'fiscal Paid' => ['Paid', Status::PAID, true],
        ];
    }

    public function testReceiptFieldsAreExcludedFromMainSignature()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $signature = $_POST['Signature'];
        $this->assertTrue($this->callback->process()->hasReceipts());

        // Другой Receipt меняет только ReceiptSignature.
        $_POST = $this->fiscalPost($this->encodedReceipts(2));
        $this->assertSame($signature, $_POST['Signature']);
        $this->assertCount(2, $this->callback->process()->getReceipts());
    }

    public function testMainSignatureIncludingReceiptIsRejected()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $_POST['Signature'] = strtoupper(md5(
            '12345' . 'paid' . $_POST['Receipt'] . $_POST['ReceiptSignature'] . self::PASSWORD
        ));
        $this->expectParserNotCalled();

        $this->expectCallbackError('Signature not valid', 403);
        $this->callback->process();
    }

    public function testOrdinaryCallbackWithRawAdditionalFields()
    {
        $_POST = $this->ordinaryPost();
        // Порядок POST не определяет порядок подписи; PaymentType в DTO станет int.
        $_POST['Total'] = '100.50';
        $_POST['PaymentType'] = '01';
        $_POST['BillNumber'] = '000123';
        $_POST['Signature'] = strtoupper(md5('12345' . 'paid' . '000123' . '100.50' . '01' . self::PASSWORD));

        $order = $this->callback->process();

        $this->assertSame('000123', $order->getBillNumber());
        $this->assertSame('100.50', $order->getTotal());
        $this->assertSame(1, $order->getPaymentType());
        $this->assertFalse($order->hasReceipts());
    }

    public function testOrdinaryCallbackWithAllAdditionalFields()
    {
        $_POST = $this->ordinaryPost() + [
            'Total' => '100.50', 'PaymentType' => '13', 'EMoneyType' => 'wallet',
            'ECI' => '5', 'Customer_IDP' => 'customer', 'Card_IDP' => 'card',
            'CardNumber' => '411111******1111', 'BillNumber' => 'bill',
            'Balance' => '0.00', 'ApprovalCode' => 'approval', 'AcquirerID' => 'acquirer',
        ];
        $_POST['Signature'] = strtoupper(md5(
            '12345' . 'paid' . 'approval' . 'bill' . '100.50' . 'acquirer' . '0.00'
            . '411111******1111' . 'card' . 'customer' . '5' . 'wallet' . '13'
            . self::PASSWORD
        ));

        $order = $this->callback->process();

        $this->assertSame('0.00', $order->getBalance());
        $this->assertSame('card', $order->getCardId());
        $this->assertSame('acquirer', $order->getAcquirerId());
    }

    public function testFiscalCallbackWithAdditionalFieldsInPostOrder()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts()) + [
            'PaymentType' => '01', 'Total' => '100.50', 'Balance' => '0.00',
            'BillNumber' => '000123', 'ApprovalCode' => 'approved', 'AcquirerID' => 'bank',
        ];
        $_POST['Signature'] = strtoupper(md5(
            '12345' . 'paid' . '01' . '100.50' . '0.00'
            . '000123' . 'approved' . 'bank' . self::PASSWORD
        ));

        $order = $this->callback->process();

        $this->assertTrue($order->hasReceipts());
        $this->assertSame('approved', $order->getApprovalCode());
        $this->assertSame('000123', $order->getBillNumber());
        $this->assertSame('100.50', $order->getTotal());
        $this->assertSame(1, $order->getPaymentType());
    }

    public function testLowercaseReceiptSignatureIsAcceptedAndPreserved()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $_POST['ReceiptSignature'] = strtolower($_POST['ReceiptSignature']);

        $this->assertSame($_POST['ReceiptSignature'], $this->callback->process()->getReceiptSignature());
    }

    public function testParserIsResolvedFromContainerAfterSignatureVerification()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $receipts = [$this->createMock(FiscalReceipt::class)];
        $parser = $this->createMock(ParserReceiptFromBase64::class);
        $parser->expects($this->once())->method('parse')->with($_POST['Receipt'])->willReturn($receipts);
        $this->container->set(ParserReceiptFromBase64::class, $parser);

        $this->assertSame($receipts, $this->callback->process()->getReceipts());
    }

    public function testArrayReceiptIsRejectedAsBadRequest()
    {
        $_POST = $this->fiscalPost($this->encodedReceipts());
        $_POST['Receipt'] = ['unexpected'];
        $this->expectParserNotCalled();

        $this->expectCallbackError('Invalid callback parameter: Receipt', 400);
        $this->callback->process();
    }

    /**
     * @dataProvider callbackModeProvider
     */
    public function testCallbackFieldOrderForEverySubset(string $mode)
    {
        $values = [
            'PaymentType' => '01', 'ECI' => '7', 'ApprovalCode' => 'approved',
            'Balance' => '0.00', 'Card_IDP' => 'card', 'Total' => '50.00',
            'Customer_IDP' => 'customer', 'BillNumber' => '000123',
            'AcquirerID' => 'bank', 'CardNumber' => '220006******6821', 'EMoneyType' => 'wallet',
        ];
        $names = array_keys($values);
        $parameter = new CallbackFieldsParameter('callbackFields', 'CallbackFields');
        $receipt = $mode === 'receipt' ? $this->encodedReceipts() : '';

        for ($mask = 0; $mask < (1 << count($names)); $mask++) {
            $requested = [];
            foreach ($names as $index => $name) {
                if ($mask & (1 << $index)) {
                    $requested[] = $name;
                }
            }
            $parameter->setValue($requested);
            $sentFields = $parameter->getValue() === '' ? [] : explode(' ', $parameter->getValue());
            $incoming = array_reverse(array_intersect_key($values, array_flip($requested)), true);
            $signatureFields = $mode === 'ordinary' ? $sentFields : array_keys($incoming);
            $signed = '12345' . 'paid';
            foreach ($signatureFields as $field) {
                $signed .= $values[$field];
            }
            $_POST = [
                'Order_ID' => '12345',
                'Status' => 'paid',
                'Signature' => strtoupper(md5($signed . self::PASSWORD)),
            ] + $incoming;
            if ($mode !== 'ordinary') {
                $_POST['Receipt'] = $receipt;
                $_POST['ReceiptSignature'] = strtoupper(hash('sha256', '12345' . 'paid' . $receipt . self::PASSWORD));
            }

            $order = $this->callback->process();
            $this->assertSame('12345', $order->getOrderId());
            $this->assertSame($mode === 'receipt', $order->hasReceipts());
        }
    }

    public function callbackModeProvider(): array
    {
        return [['ordinary'], ['empty receipt'], ['receipt']];
    }

    public function testCapturedPostOrderWithTestPasswordIsAccepted()
    {
        $_POST = $this->preauthorizationPost();
        $this->expectParserNotCalled();

        $this->assertSame('2609280002_18', $this->callback->process()->getOrderId());
    }

    public function testFiscalCallbackRejectsReorderedFieldsWithoutResigning()
    {
        $_POST = array_reverse($this->preauthorizationPost(), true);
        $this->expectParserNotCalled();

        $this->expectCallbackError('Signature not valid', 403);
        $this->callback->process();
    }

    public function testFiscalCallbackRejectsSignatureInOutgoingFieldsOrder()
    {
        $_POST = $this->preauthorizationPost();
        $_POST['Signature'] = '187B9B6C50ED56AF90BF35D6CCABB56D';
        $this->expectParserNotCalled();

        $this->expectCallbackError('Signature not valid', 403);
        $this->callback->process();
    }

    private function preauthorizationPost(): array
    {
        // Порядок из реального POST; подписи пересчитаны с тестовым паролем secret.
        return [
            'Order_ID' => '2609280002_18',
            'Status' => 'authorized',
            'ApprovalCode' => '6559F0',
            'BillNumber' => '627118788812',
            'Total' => '50.00',
            'ECI' => '7',
            'CardNumber' => '220006******6821',
            'PaymentType' => '1',
            'AcquirerID' => '35',
            'Signature' => 'E05DBE0A71671821E70C30B97E0E4465',
            'Receipt' => '',
            'ReceiptSignature' => '413D66E8FD1B98B6893EB557AF434E5B6255582D24CC4654BBF25C9454DE3327',
        ];
    }

    private function ordinaryPost(string $status = 'paid'): array
    {
        return [
            'Order_ID' => '12345',
            'Status' => $status,
            'Signature' => strtoupper(md5('12345' . $status . self::PASSWORD)),
        ];
    }

    private function fiscalPost(string $receipt, string $status = 'paid'): array
    {
        return $this->ordinaryPost($status) + [
            'Receipt' => $receipt,
            'ReceiptSignature' => strtoupper(hash('sha256', '12345' . $status . $receipt . self::PASSWORD)),
        ];
    }

    private function expectCallbackError(string $message, int $code): void
    {
        $this->expectException(UnitellerRuntimeException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);
    }

    private function expectParserNotCalled(): void
    {
        $parser = $this->createMock(ParserReceiptFromBase64::class);
        $parser->expects($this->never())->method('parse');
        $this->container->set(ParserReceiptFromBase64::class, $parser);
    }

    private function encodedReceipts(int $count = 1): string
    {
        $receipts = [];
        for ($i = 1; $i <= $count; $i++) {
            $receipts[] = [
                'fiscal' => [
                    'id' => (string)$i,
                    'date' => '2023-01-01',
                    'type' => 1,
                    'ecr' => ['sn' => 'SN1', 'rn' => 'RN1', 'fs' => 'FS1'],
                    'company' => ['name' => 'Company 1', 'inn' => '1111111111'],
                    'fdo' => ['name' => 'FDO 1', 'inn' => '111111111', 'www' => 'fdo1.test'],
                    'register' => [
                        'fiscal_number' => 'FN' . $i,
                        'shift_number' => 1,
                        'shift_index' => $i,
                        'fiscal_date' => '2023-01-01',
                        'fiscal_attr' => 'attr1',
                        'fdo_date' => '2023-01-01',
                        'fdo_attr' => 'fdo_attr1',
                    ],
                ],
                'taxmode' => 1,
                'lines' => [],
                'payments' => [],
                'total' => 20,
            ];
        }

        return base64_encode(json_encode($receipts));
    }
}
