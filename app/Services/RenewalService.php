<?php

namespace App\Services;

use App\Models\Policy;
use App\Models\RenewalNotification;
use App\Models\RenewalRule;
use Illuminate\Support\Collection;

class RenewalService
{
    public function candidates(array $days = [30, 15, 7]): Collection
    {
        return Policy::query()
            ->with(['client', 'broker'])
            ->whereIn('status', ['active', 'renewed'])
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereDate('end_date', '<=', now()->copy()->addDays(max($days))->toDateString())
            ->orderBy('end_date')
            ->get()
            ->filter(function (Policy $policy) use ($days) {
                $remaining = now()->startOfDay()->diffInDays($policy->end_date->startOfDay(), false);

                return in_array($remaining, $days, true);
            })
            ->values();
    }

    public function scheduleReminders(): int
    {
        $created = 0;

        foreach ($this->candidates([30, 15, 7]) as $policy) {
            $remaining = now()->startOfDay()->diffInDays($policy->end_date->startOfDay(), false);
            $rule = $this->ruleForPolicy($policy);

            $channel = 'email';
            if ($remaining === 7) {
                $channel = 'whatsapp';
            } elseif ($remaining === 15) {
                $channel = 'sms';
            }

            $exists = RenewalNotification::query()
                ->where('policy_id', $policy->id)
                ->where('days_before', $remaining)
                ->where('channel', $channel)
                ->whereDate('scheduled_for', now()->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            RenewalNotification::create([
                'policy_id' => $policy->id,
                'channel' => $channel,
                'days_before' => $remaining,
                'status' => 'pending',
                'scheduled_for' => now(),
                'payload' => [
                    'policy_no' => $policy->policy_no,
                    'client_name' => $policy->client?->name,
                    'days_before' => $remaining,
                    'recommended_quote' => $this->suggestPrice($policy),
                    'auto_quote' => (bool) ($rule?->auto_quote ?? true),
                ],
            ]);

            $policy->forceFill(['renewal_notified_at' => now()])->save();
            $created++;
        }

        return $created;
    }

    public function suggestPrice(Policy $policy): float
    {
        $rule = $this->ruleForPolicy($policy);
        $claimsRatio = $this->claimsRatio($policy);
        $base = (float) $policy->net_premium;

        $loadingPct = (float) ($rule?->risk_loading_pct ?? 0);
        $discountPct = (float) ($rule?->claims_discount_pct ?? 0);

        if ($claimsRatio > 0.6) {
            $loadingPct += 10;
        } elseif ($claimsRatio < 0.2) {
            $discountPct += 5;
        }

        $quote = $base * (1 + ($loadingPct / 100));
        $quote = $quote * (1 - ($discountPct / 100));

        return round(max($quote, 0), 2);
    }

    public function bulkRenew(array $policyIds, int $issuedBy): Collection
    {
        $renewed = collect();

        Policy::query()
            ->whereIn('id', $policyIds)
            ->chunkById(50, function ($policies) use ($issuedBy, &$renewed) {
                foreach ($policies as $policy) {
                    $newPolicy = $this->renew($policy, $issuedBy);
                    $renewed->push($newPolicy);
                }
            });

        return $renewed;
    }

    public function renew(Policy $policy, int $issuedBy): Policy
    {
        $quoted = $this->suggestPrice($policy);

        $newPolicy = Policy::create([
            'policy_no' => strtoupper($policy->type).'-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'client_id' => $policy->client_id,
            'broker_id' => $policy->broker_id,
            'branch_id' => $policy->branch_id,
            'issued_by' => $issuedBy,
            'type' => $policy->type,
            'status' => 'active',
            'premium' => $quoted,
            'discount_pct' => $policy->discount_pct,
            'loading_pct' => $policy->loading_pct,
            'net_premium' => $quoted,
            'start_date' => $policy->end_date->copy()->addDay()->toDateString(),
            'end_date' => $policy->end_date->copy()->addYear()->toDateString(),
            'issued_at' => now(),
            'renewed_from_policy_id' => $policy->id,
            'data' => $policy->data,
        ]);

        $policy->forceFill(['status' => 'renewed', 'renewal_quoted_at' => now()])->save();

        return $newPolicy;
    }

    private function ruleForPolicy(Policy $policy): ?RenewalRule
    {
        return RenewalRule::query()
            ->where('product_type', $policy->type)
            ->where('is_active', true)
            ->first();
    }

    private function claimsRatio(Policy $policy): float
    {
        $premium = (float) $policy->net_premium;
        if ($premium <= 0) {
            return 0;
        }

        $claimsPaid = (float) $policy->claims()->where('status', 'paid')->sum('paid_amount');

        return round($claimsPaid / $premium, 4);
    }
}
