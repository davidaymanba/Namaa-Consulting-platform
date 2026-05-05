<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ApprovalFlowRuleSeeder::class,
            RenewalRuleSeeder::class,
            ClaimAutomationRuleSeeder::class,
            PaymentReminderRuleSeeder::class,
            ReinsuranceRuleSeeder::class,
            CoreInsuranceSeeder::class,
        ]);
    }
}
