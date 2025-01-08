<?php

namespace App;

use Exception;

class StatusChecker
{
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getStatus(string $email, string $transactionId): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        if (empty($transactionId)) {
            throw new Exception('A transaction ID is required.');
        }

        $data = [
            'email' => $email,
            'transaction_id' => $transactionId,
        ];

        return $this->httpClient->request('GET', '/status', $data);
    }
}