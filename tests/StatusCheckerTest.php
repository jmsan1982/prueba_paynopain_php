<?php

use App\StatusChecker;
use App\HttpClient;
use PHPUnit\Framework\TestCase;

class StatusCheckerTest extends TestCase
{
    private $statusChecker;

    protected function setUp(): void
    {
        // Configurar HttpClient mock
        $httpClientMock = $this->createMock(HttpClient::class);

        // Configurar el comportamiento del mock para solicitudes exitosas
        $httpClientMock->method('request')
            ->willReturnCallback(function ($method, $endpoint, $data) {
                if ($endpoint === '/status') {
                    // Simular respuesta exitosa
                    return [
                        'transaction_id' => $data['transaction_id'],
                        'remaining_amount' => 500,
                        'status' => 'success',
                    ];
                }
                throw new Exception('Endpoint not found.');
            });

        // Inicializar StatusChecker con el mock
        $this->statusChecker = new StatusChecker($httpClientMock);
    }

    /**
     * @depends PaymentProcessorTest::testSuccessfulPayment
     */
    public function testGetStatusSuccess(string $transaction_id): void
    {
        $response = $this->statusChecker->getStatus('test@test.com', $transaction_id);

        $this->assertArrayHasKey('transaction_id', $response);
        $this->assertEquals($transaction_id, $response['transaction_id']);
        $this->assertArrayHasKey('remaining_amount', $response);
        $this->assertEquals(500, $response['remaining_amount']);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    public function testGetStatusWithInvalidEmail(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid email format.');

        $this->statusChecker->getStatus('invalid-email', 'txn_12345');
    }

    public function testGetStatusWithMissingTransactionId(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A transaction ID is required.');

        $this->statusChecker->getStatus('test@test.com', '');
    }

    /**
     * @depends PaymentProcessorTest::testSuccessfulPayment
     */
    public function testGetStatusWithInvalidEndpoint(string $transaction_id): void
    {
        $httpClientMock = $this->createMock(HttpClient::class);
        $httpClientMock->method('request')
            ->willThrowException(new Exception('Endpoint not found.'));

        $this->statusChecker = new StatusChecker($httpClientMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Endpoint not found.');

        $this->statusChecker->getStatus('test@test.com', $transaction_id);
    }
}

