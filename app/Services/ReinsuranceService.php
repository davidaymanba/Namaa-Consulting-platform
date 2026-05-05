<?php

namespace App\Services;

use App\Exports\ReinsuranceBordereauxExport;
use App\Models\BordereauxBatch;
use App\Models\Policy;
use App\Models\ReinsuranceAlert;
use App\Models\ReinsuranceDistribution;
use App\Models\ReinsuranceTreaty;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
            $premium = (float) $policy->net_premium;
            $share = match ($treaty->type) {
                'quota_share' => round($premium * ((float) $treaty->share_pct / 100), 2),
                'excess_of_loss' => round(max(0, $premium - (float) $treaty->retention) * ((float) $treaty->share_pct / 100), 2),
                default => 0.0,
            };

            if ($treaty->limit_amount !== null && $share > (float) $treaty->limit_amount) {
                $share = (float) $treaty->limit_amount;
                $this->createAlert($policy, $treaty, 'limit_breached', 'RI share capped by treaty limit.', [
                    'policy_premium' => $premium,
                    'limit_amount' => (float) $treaty->limit_amount,
                ]);
            }

            if ($premium > (float) $treaty->retention) {
                $this->createAlert($policy, $treaty, 'retention_breached', 'Policy premium exceeded treaty retention threshold.', [
                    'policy_premium' => $premium,
                    'retention' => (float) $treaty->retention,
                ]);
            }

            ReinsuranceDistribution::create([
                'policy_id' => $policy->id,
                'reinsurance_treaty_id' => $treaty->id,
                'policy_premium' => $policy->net_premium,
                'ri_share_amount' => $share,
            ]);
        }
    }

    public function generateBordereauxBatch(?string $startDate = null, ?string $endDate = null): BordereauxBatch
    {
        $startDate ??= now()->startOfMonth()->toDateString();
        $endDate ??= now()->endOfMonth()->toDateString();

        $batch = BordereauxBatch::create([
            'period_start' => $startDate,
            'period_end' => $endDate,
            'status' => 'pending',
            'total_records' => 0,
            'total_ri_share' => 0,
            'notes' => 'Auto-generated reinsurance bordereaux batch.',
        ]);

        $fileName = 'bordereaux_'.$batch->period_start->format('Y_m').'.xlsx';
        $filePath = 'reinsurance/'.$fileName;

        Excel::store(new ReinsuranceBordereauxExport($startDate, $endDate), $filePath, 'public');

        $records = ReinsuranceDistribution::query()
            ->whereHas('policy', fn ($query) => $query->whereBetween('issued_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']))
            ->count();
        $totalRiShare = (float) ReinsuranceDistribution::query()
            ->whereHas('policy', fn ($query) => $query->whereBetween('issued_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']))
            ->sum('ri_share_amount');

        $batch->update([
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'sent',
            'total_records' => $records,
            'total_ri_share' => $totalRiShare,
            'sent_at' => now(),
        ]);

        ReinsuranceTreaty::query()
            ->where('is_active', true)
            ->update(['last_bordereaux_sent_at' => now()]);

        Storage::disk('public')->exists($filePath);

        return $batch->fresh();
    }

    public function alerts()
    {
        return ReinsuranceAlert::query()
            ->with(['policy.client', 'treaty'])
            ->latest()
            ->paginate(20);
    }

    public function resolveAlert(ReinsuranceAlert $alert, int $resolvedBy): ReinsuranceAlert
    {
        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
        ]);

        return $alert->fresh();
    }

    private function createAlert(Policy $policy, ReinsuranceTreaty $treaty, string $type, string $message, array $context = []): void
    {
        ReinsuranceAlert::create([
            'policy_id' => $policy->id,
            'reinsurance_treaty_id' => $treaty->id,
            'type' => $type,
            'message' => $message,
            'status' => 'pending',
            'context' => $context,
        ]);
    }
}
