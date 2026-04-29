<?php

namespace App\Services;

use Carbon\Carbon;

class QuotationCalculator
{
    private const FIXED_RATE = 3;

    private const AGE_LOADS = [
        [18, 30, 0.6],
        [31, 40, 0.7],
        [41, 50, 0.8],
        [51, 60, 0.9],
        [61, 70, 1.0],
    ];

    public function calculate(array $ages, string $startDate, string $endDate): float
    {
        $tripLength = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $total = 0;

        foreach ($ages as $age) {
            $total += self::FIXED_RATE * $this->getAgeLoad($age) * $tripLength;
        }

        return round($total, 2);
    }

    private function getAgeLoad(int $age): float
    {
        foreach (self::AGE_LOADS as [$min, $max, $load]) {
            if ($age >= $min && $age <= $max) {
                return $load;
            }
        }

        return 0;
    }
}
