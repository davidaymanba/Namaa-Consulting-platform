<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimAutomationRule;
use App\Models\Policy;
use Illuminate\Support\Arr;

class ClaimAutomationService
{
    public function startWizard(Policy $policy, array $payload = []): array
    {
        return [
            'policy_id' => $policy->id,
            'policy_no' => $policy->policy_no,
            'current_step' => 1,
            'steps' => [
                'incident_details',
                'claim_details',
                'documents',
                'review',
            ],
            'state' => array_merge([
                'policy_id' => $policy->id,
                'steps_completed' => [],
            ], $payload),
        ];
    }

    public function saveStep(Claim $claim, string $step, array $data): Claim
    {
        $state = $claim->wizard_state ?? [];
        $state['steps_completed'] = array_values(array_unique(array_merge($state['steps_completed'] ?? [], [$step])));
        $state[$step] = $data;
        $state['updated_at'] = now()->toDateTimeString();

        $claim->forceFill(['wizard_state' => $state])->save();

        return $claim->fresh();
    }

    public function coverageCheck(Claim $claim): array
    {
        $policy = $claim->policy;
        $policyData = $policy?->data ?? [];
        $rules = $this->ruleConfig('coverage');

        $reasons = [];
        $covered = true;

        if (! $policy) {
            $covered = false;
            $reasons[] = 'Policy not found.';
        }

        if ($policy && ($claim->incident_date < $policy->start_date || $claim->incident_date > $policy->end_date)) {
            $covered = false;
            $reasons[] = 'Incident date is outside policy period.';
        }

        $reportedLateDays = $claim->incident_date?->diffInDays($claim->report_date, false) ?? 0;
        $lateThreshold = (int) Arr::get($rules, 'report_days_max', 30);
        if ($reportedLateDays > $lateThreshold) {
            $covered = false;
            $reasons[] = 'Claim was reported after the allowed reporting window.';
        }

        $limit = (float) Arr::get($policyData, 'market_value', 0);
        $claimLimit = (float) Arr::get($rules, 'max_claim_pct_of_limit', 100);
        if ($limit > 0 && $claim->claimed_amount > ($limit * ($claimLimit / 100))) {
            $covered = false;
            $reasons[] = 'Claim amount exceeds coverage limit heuristic.';
        }

        $claim->forceFill([
            'coverage_status' => $covered ? 'covered' : 'not_covered',
            'coverage_checked_at' => now(),
        ])->save();

        return [
            'covered' => $covered,
            'reasons' => $reasons,
            'policy_no' => $policy?->policy_no,
            'coverage_status' => $covered ? 'covered' : 'not_covered',
        ];
    }

    public function fraudFlags(Claim $claim): array
    {
        $rules = $this->ruleConfig('fraud');
        $flags = [];
        $score = 0;

        $duplicateWindowDays = (int) Arr::get($rules, 'duplicate_window_days', 30);
        $duplicateClaims = Claim::query()
            ->where('policy_id', $claim->policy_id)
            ->where('id', '!=', $claim->id)
            ->whereDate('incident_date', $claim->incident_date?->toDateString())
            ->where('claimed_amount', $claim->claimed_amount)
            ->count();

        if ($duplicateClaims > 0) {
            $flags[] = 'duplicate_claim';
            $score += 35;
        }

        $recentClaimsThreshold = (int) Arr::get($rules, 'recent_claims_threshold', 3);
        $recentClaims = Claim::query()
            ->where('policy_id', $claim->policy_id)
            ->where('id', '!=', $claim->id)
            ->where('created_at', '>=', now()->subDays($duplicateWindowDays))
            ->count();

        if ($recentClaims >= $recentClaimsThreshold) {
            $flags[] = 'frequent_claims';
            $score += 25;
        }

        $premiumFactor = (float) Arr::get($rules, 'large_claim_multiplier', 1.5);
        $policyPremium = (float) ($claim->policy?->net_premium ?? 0);
        if ($policyPremium > 0 && $claim->claimed_amount > ($policyPremium * $premiumFactor)) {
            $flags[] = 'amount_outlier';
            $score += 20;
        }

        if (($claim->is_large_claim ?? false) === true) {
            $flags[] = 'large_claim';
            $score += 10;
        }

        $score = min($score, 100);

        $claim->forceFill([
            'fraud_score' => $score,
            'fraud_flags' => $flags,
        ])->save();

        return [
            'fraud_score' => $score,
            'fraud_flags' => $flags,
            'severity' => $score >= 60 ? 'high' : ($score >= 30 ? 'medium' : 'low'),
        ];
    }

    public function nextStep(Claim $claim): string
    {
        $completed = $claim->wizard_state['steps_completed'] ?? [];
        $steps = ['incident_details', 'claim_details', 'documents', 'review'];

        foreach ($steps as $step) {
            if (! in_array($step, $completed, true)) {
                return $step;
            }
        }

        return 'complete';
    }

    private function ruleConfig(string $group): array
    {
        return (array) (ClaimAutomationRule::query()
            ->where('is_active', true)
            ->where('code', $group)
            ->first()?->config ?? []);
    }
}
