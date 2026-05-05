<?php

namespace App\Services;

use App\Models\Policy;
use App\Models\ReinsuranceDistribution;
use App\Models\ReinsuranceTreaty;

class ReinsuranceService
{
    public function distributeForPolicy(Policy $policy): void
    {
        $treaties = ReinsuranceTreaty::query()
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', now());
            })
            ->get();

        foreach ($treaties as $treaty) {
            $share = round(((float) $policy->net_premium * ((float) $treaty->share_pct / 100)), 2);

            ReinsuranceDistribution::create([
                'policy_id' => $policy->id,
                'reinsurance_treaty_id' => $treaty->id,
                'policy_premium' => $policy->net_premium,
                'ri_share_amount' => $share,
            ]);
        }
    }
}
