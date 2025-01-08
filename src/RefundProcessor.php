<?php

namespace App;

use Exception;

class RefundProcessor
{
    private $httpClient;
    private $statusChecker;

    public function  __construct(HttpClient $httpClient, StatusChecker $statusChecker)
    {
        $this->httpClient = $httpClient;
        $this->statusChecker = $statusChecker;
    }

    public function requestRefund(string $email, string $transactionId, ?int $amount = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        if (empty($transactionId)) {
            throw new Exception('Transaction ID is required.');
        }

        $data = [
            'email' => $email,
            'transaction_id' => $transactionId,
        ];

        if ($amount !== null) {
            if ($amount <= 0) {
                throw new Exception('Refund amount must be a positive integer.');
            }

            $transactionDetails = $this->statusChecker->getStatus($email, $transactionId);

            if (!isset($transactionDetails['remaining_amount'])) {
                throw new Exception('The specified transaction could not be found.');
            }

            $remainingAmount = (int) $transactionDetails['remaining_amount'];

            if ($amount > $remainingAmount) {
                throw new Exception('The amount cannot be greater than the transaction amount.');
            }

            $data['amount'] = $amount;
        }

        return $this->httpClient->request('POST', 'refund', $data);
    }
}