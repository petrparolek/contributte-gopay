<?php

/**
 * Test: Markette\Gopay\Service\PreAuthorizedPaymentService
 *
 * @testCase
 */

use Markette\Gopay\Gopay;
use Markette\Gopay\Service\PreAuthorizedPaymentService;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class PreAuthorizedPaymentServiceTest extends BasePaymentTestCase
{

    public function testRecurrentPay()
    {
        $gopay = $this->createPreAuthorizedPaymentGopay();

        $service = new PreAuthorizedPaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        $response = $service->payPreAuthorized($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::same('https://testgw.gopay.cz/gw/pay-full-v2?sessionInfo.targetGoId=1234567890&sessionInfo.paymentSessionId=3000000001&sessionInfo.encryptedSignature=999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response->getUrl()
        );
        Assert::same(302, $response->getCode());
    }

    public function testRecurrentPayInline()
    {
        $gopay = $this->createPreAuthorizedPaymentGopay();

        $service = new PreAuthorizedPaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        $response = $service->payPreAuthorizedInline($payment, $gopay::METHOD_CARD_GPKB, $this->createNullCallback());

        Assert::type('array', $response);
        Assert::count(2, $response);
        Assert::same('https://testgw.gopay.cz/gw/v3/3000000001',
            $response['url']
        );

        Assert::same('999c4a90f42af5bdd9b5b7eaff43f27eb671b03a1efd4662b729dd21b9be41c22d5b25fe5955ff8d',
            $response['signature']
        );
    }

    public function testCreatePayment()
    {
        $gopay = Mockery::mock(Gopay::class);

        $service = new PreAuthorizedPaymentService($gopay);
        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        Assert::type('Markette\Gopay\Entity\PreAuthorizedPayment', $payment);
    }

    public function testPayThrowsException()
    {
        $gopay = $this->createPreAuthorizedPaymentGopay();
        $exmsg = "Fatal error during paying";
        $gopay->getSoap()->shouldReceive('createPreAutorizedPayment')->twice()->andThrow('Exception', $exmsg);

        $service = new PreAuthorizedPaymentService($gopay);
        $service->addChannel($gopay::METHOD_CARD_GPKB, 'KB');

        $payment = $service->createPayment(['sum' => 999, 'customer' => []]);

        Assert::throws(function () use ($service, $payment) {
            $response = $service->payPreAuthorized($payment, Gopay::METHOD_CARD_GPKB, function () {
            });
        }, 'Markette\Gopay\Exception\GopayException', $exmsg);

        Assert::throws(function () use ($service, $payment) {
            $response = $service->payPreAuthorizedInline($payment, Gopay::METHOD_CARD_GPKB, function () {
            });
        }, 'Markette\Gopay\Exception\GopayException', $exmsg);
    }
}

$test = new PreAuthorizedPaymentServiceTest();
$test->run();