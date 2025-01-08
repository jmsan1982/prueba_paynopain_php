<?php

use App\DiscountManager;
use App\HttpClient;
use App\PaymentProcessor;
use PHPUnit\Framework\TestCase;

class PaymentProcessorTest extends TestCase
{
    private $paymentProcessor;
    private $httpClient;
    private $discountManager;

    protected function setUp(): void
    {
        $this->httpClient = new HttpClient(
            'https://m62a4fxd5grnmt2743qjs7dheq0miuar.lambda-url.eu-west-1.on.aws/',
            'A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0E1F2'
        );

        $this->discountManager = new DiscountManager();
        $this->paymentProcessor = new PaymentProcessor($this->httpClient, $this->discountManager);
    }

    public function testSuccessfulPayment(): string
    {
        $response = $this->paymentProcessor->processPayment(
            'test@test.es',
            1235,
            'merchan_id_' . uniqid()
        );

        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('failed', $response['status']);

        if (isset($response['transaction_id'])) {
            return $response['transaction_id'];
        }

        $this->fail('Transaction ID was not returned in the response.');
    }

    public function testFailedPaymentDueToLowAmount(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The payment must be greater than €10');
        $this->paymentProcessor->processPayment('test@test.es', 100, 'merchan_id_low');
    }

    public function testProcessPaymentWithInvalidEmail(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid email format.');
        $this->paymentProcessor->processPayment('@test', 2000, 'merchan_id_');
    }
}
