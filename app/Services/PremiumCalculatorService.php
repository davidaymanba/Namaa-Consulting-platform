<?php

namespace App\Services;

use App\Models\TariffRate;

class PremiumCalculatorService
{
    public function forCar(array $data): array
    {
        $marketValue = (float) ($data['market_value'] ?? 0);
        $driverAge = (int) ($data['driver_age'] ?? 30);
        $insuranceType = $data['insurance_type'] ?? 'mandatory';
        $discount = (float) ($data['discount_pct'] ?? 0);
        $loading = (float) ($data['loading_pct'] ?? 0);

        $tariff = TariffRate::query()
            ->where('insurance_type', 'car')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $baseRate = (float) ($tariff?->base_rate ?? 0.025);
        $minPremium = (float) ($tariff?->min_premium ?? 120);

        if ($insuranceType === 'comprehensive') {
            $baseRate += 0.0100;
        }

        if ($driverAge < 25) {
            $baseRate += 0.0075;
        }

        $basePremium = max($marketValue * $baseRate, $minPremium);
        $premiumAfterLoading = $basePremium * (1 + ($loading / 100));
        $netPremium = $premiumAfterLoading * (1 - ($discount / 100));

        return [
            'base_rate' => round($baseRate, 4),
            'premium' => round($basePremium, 2),
            'net_premium' => round(max($netPremium, $minPremium), 2),
            'discount_pct' => $discount,
            'loading_pct' => $loading,
        ];
    }

    public function generateInstallments(float $amount, string $mode, string $startDate): array
    {
        $mode = strtolower($mode);
        $count = match ($mode) {
            'monthly' => 12,
            'quarterly' => 4,
            default => 1,
        };

        $monthsStep = match ($mode) {
            'monthly' => 1,
            'quarterly' => 3,
            default => 12,
        };

        $each = round($amount / $count, 2);
        $installments = [];

        for ($i = 0; $i < $count; $i++) {
            $installments[] = [
                'sequence' => $i + 1,
                'amount' => $i === $count - 1 ? round($amount - ($each * ($count - 1)), 2) : $each,
                'due_date' => now()->parse($startDate)->addMonths($i * $monthsStep)->toDateString(),
            ];
        }

        return $installments;
    }
}
