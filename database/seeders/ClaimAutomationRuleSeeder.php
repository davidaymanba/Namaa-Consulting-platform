<?php

namespace Database\Seeders;

use App\Models\ClaimAutomationRule;
use Illuminate\Database\Seeder;

class ClaimAutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        ClaimAutomationRule::updateOrCreate(
            ['code' => 'coverage'],
            [
                'label' => 'Coverage validation defaults',
                'config' => [
                    'report_days_max' => 30,
                    'max_claim_pct_of_limit' => 100,
                ],
                'is_active' => true,
            ]
        );

        ClaimAutomationRule::updateOrCreate(
            ['code' => 'fraud'],
            [
                'label' => 'Fraud detection defaults',
                'config' => [
                    'duplicate_window_days' => 30,
                    'recent_claims_threshold' => 3,
                    'large_claim_multiplier' => 1.5,
                ],
                'is_active' => true,
            ]
        );
    }
}
