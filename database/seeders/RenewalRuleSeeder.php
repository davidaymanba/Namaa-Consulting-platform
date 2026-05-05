<?php

namespace Database\Seeders;

use App\Models\RenewalRule;
use Illuminate\Database\Seeder;

class RenewalRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['product_type' => 'health', 'notify_days_before' => 30, 'auto_quote' => true, 'bulk_eligible' => true, 'risk_loading_pct' => 5, 'claims_discount_pct' => 3, 'is_active' => true],
            ['product_type' => 'car', 'notify_days_before' => 30, 'auto_quote' => true, 'bulk_eligible' => true, 'risk_loading_pct' => 4, 'claims_discount_pct' => 2, 'is_active' => true],
            ['product_type' => 'fire', 'notify_days_before' => 30, 'auto_quote' => true, 'bulk_eligible' => true, 'risk_loading_pct' => 6, 'claims_discount_pct' => 2, 'is_active' => true],
            ['product_type' => 'marine', 'notify_days_before' => 30, 'auto_quote' => true, 'bulk_eligible' => false, 'risk_loading_pct' => 7, 'claims_discount_pct' => 1, 'is_active' => true],
            ['product_type' => 'engineering', 'notify_days_before' => 30, 'auto_quote' => true, 'bulk_eligible' => false, 'risk_loading_pct' => 8, 'claims_discount_pct' => 1, 'is_active' => true],
            ['product_type' => 'liability', 'notify_days_before' => 30, 'auto_quote' => true, 'bulk_eligible' => true, 'risk_loading_pct' => 5, 'claims_discount_pct' => 2, 'is_active' => true],
        ];

        foreach ($rules as $rule) {
            RenewalRule::updateOrCreate(
                ['product_type' => $rule['product_type']],
                $rule
            );
        }
    }
}
