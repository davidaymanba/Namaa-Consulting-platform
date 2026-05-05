<?php

namespace Database\Seeders;

use App\Models\ReinsuranceTreaty;
use Illuminate\Database\Seeder;

class ReinsuranceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $treaties = [
            ['reinsurer' => 'Saudi Re', 'type' => 'quota_share', 'share_pct' => 25, 'retention' => 5000, 'limit_amount' => 25000, 'rules' => ['board_notify' => true], 'effective_from' => now()->subYear()->toDateString(), 'effective_to' => null, 'is_active' => true],
            ['reinsurer' => 'Arab Re', 'type' => 'excess_of_loss', 'share_pct' => 10, 'retention' => 10000, 'limit_amount' => 40000, 'rules' => ['board_notify' => true], 'effective_from' => now()->subYear()->toDateString(), 'effective_to' => null, 'is_active' => true],
        ];

        foreach ($treaties as $treaty) {
            ReinsuranceTreaty::updateOrCreate(
                ['reinsurer' => $treaty['reinsurer'], 'type' => $treaty['type']],
                $treaty
            );
        }
    }
}
