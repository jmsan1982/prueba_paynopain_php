<?php

use App\DiscountManager;
use App\HttpClient;
use App\PaymentProcessor;
use PHPUnit\Framework\TestCase;

class DiscountProcessorTest extends TestCase
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

    public function testPaymentWithFutureDiscount(): void
    {
        $this->discountManager->setDiscount('merchant_with_future_discount', 10, '2025-12-31');
        $response = $this->paymentProcessor->processPayment(
            'test@test.es',
            2000,
            'merchan_id_' . uniqid()
        );
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('failed', $response['status']);
    }

    public function testPaymentWithValidDiscount(): void
    {
        $this->discountManager->setDiscount('merchant_with_discount', 10, date('Y-m-d'));
        $response = $this->paymentProcessor->processPayment(
            'test@test.es',
            2000,
            'merchan_id_' . uniqid()
        );
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('failed', $response['status']);
    }

    public function testPaymentWithFullDiscount(): void
    {
        $this->discountManager->setDiscount('merchant_full_discount', 100, date('Y-m-d'));
        $response = $this->paymentProcessor->processPayment(
            'test@test.es',
            2000,
            'merchan_id_' . uniqid()
        );
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('failed', $response['status']);
    }
}
