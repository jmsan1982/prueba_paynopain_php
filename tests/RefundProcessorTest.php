<?php

use App\HttpClient;
use App\RefundProcessor;
use App\StatusChecker;
use PHPUnit\Framework\TestCase;

class RefundProcessorTest extends TestCase
{
    private $refundProcessor;
    private $httpClient;
    private $statusChecker;

    protected function setUp(): void
    {
        $this->httpClient = new HttpClient(
            'https://m62a4fxd5grnmt2743qjs7dheq0miuar.lambda-url.eu-west-1.on.aws/',
            'A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0E1F2'
        );

        $this->statusChecker = new StatusChecker($this->httpClient);
        $this->refundProcessor = new RefundProcessor($this->httpClient, $this->statusChecker);
    }

    /**
     * @depends PaymentProcessorTest::testSuccessfulPayment
     */
    public function testRefundRequest(string $transaction_id): void
    {
        $this->assertNotEmpty($transaction_id, 'Transaction ID is not set from the previous test.');

        $response = $this->refundProcessor->requestRefund('test@test.es', $transaction_id, 50);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    public function testRequestRefundWithInvalidEmail(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid email format.');
        $this->refundProcessor->requestRefund('testtest', 'txn_12345', 100);
    }

    public function testRequestRefundWithMissingTransactionId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Transaction ID is required.');
        $this->refundProcessor->requestRefund('test@test.es', '', 100);
    }
}
