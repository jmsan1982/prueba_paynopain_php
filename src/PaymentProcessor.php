<?php

namespace App;

use Exception;

class PaymentProcessor
{
    private $httpClient;
    private $discountManager;

    public function __construct(HttpClient $httpClient, DiscountManager $discountManager)
    {
        $this->httpClient = $httpClient;
        $this->discountManager = $discountManager;
    }

    public function processPayment(string $email, int $amount, string $merchantIdentifier){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        if ($amount <= 1000) {
            throw new Exception('The payment must be greater than €10');
        }

        if (empty($merchantIdentifier) || !is_string($merchantIdentifier)) {
            throw new Exception('Merchant identifier is required and must be a string.');
        }

        $discount = $this->discountManager->getDiscount($merchantIdentifier);

        if ($discount) {
            $currentDate = date('Y-m-d');

            if ($currentDate >= $discount['startDate']) {
                $discountAmount = ($amount * $discount['discountPercentage']) / 100;
                $amount -= $discountAmount;
            }
        }

        $data = [
            'email' => $email,
            'amount' => $amount,
            'merchant_identifier' => $merchantIdentifier,
        ];

        return $this->httpClient->request('POST', '/payment', $data);
    }
}