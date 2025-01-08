<?php

namespace App;

use Exception;

class DiscountManager
{
    private $discounts = [];

    public function setDiscount(string $merchantIdentifier, int $discountPercentage, string $startDate)
    {
        if ($discountPercentage <= 0 || $discountPercentage > 100) {
            throw new Exception('Invalid discount percentage.');
        }

        if (!strtotime($startDate)) {
            throw new Exception('Invalid start date.');
        }

        $this->discounts[$merchantIdentifier] = [
            'discountPercentage' => $discountPercentage,
            'startDate' => $startDate
        ];
    }

    public function getDiscount(string $merchantIdentifier)
    {
        return $this->discounts[$merchantIdentifier] ?? null;
    }
}