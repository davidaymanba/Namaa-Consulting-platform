<?php

namespace App\Services;

use App\Models\PaymentReminder;
use App\Models\PaymentReminderRule;
use App\Models\PolicyInstallment;
use Illuminate\Support\Collection;

class PaymentCollectionService
{
    public function scheduleReminders(): int
    {
        $created = 0;
        $rules = $this->activeRules();

        PolicyInstallment::query()
            ->with('policy.client')
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_date', '<=', now()->copy()->addDays(30)->toDateString())
            ->chunkById(100, function ($installments) use ($rules, &$created) {
                foreach ($installments as $installment) {
                    $daysUntilDue = now()->startOfDay()->diffInDays($installment->due_date->startOfDay(), false);
                    $daysOverdue = $daysUntilDue < 0 ? abs($daysUntilDue) : 0;

                    $installment->forceFill([
                        'status' => $daysOverdue > 0 ? 'overdue' : $installment->status,
                        'overdue_days' => $daysOverdue,
                    ])->save();

                    foreach ($rules as $rule) {
                        if (! $this->shouldSchedule($daysUntilDue, $daysOverdue, $rule['days_offset'])) {
                            continue;
                        }

                        $channel = $rule['channel'];
                        $offset = $rule['days_offset'];

                        $exists = PaymentReminder::query()
                            ->where('policy_installment_id', $installment->id)
                            ->where('days_offset', $offset)
                            ->where('channel', $channel)
                            ->whereDate('scheduled_for', now()->toDateString())
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        PaymentReminder::create([
                            'policy_installment_id' => $installment->id,
                            'channel' => $channel,
                            'days_offset' => $offset,
                            'status' => 'pending',
                            'scheduled_for' => now(),
                            'payload' => [
                                'policy_no' => $installment->policy?->policy_no,
                                'client_name' => $installment->policy?->client?->name,
                                'installment_sequence' => $installment->sequence,
                                'due_date' => $installment->due_date?->toDateString(),
                                'amount' => (float) $installment->amount,
                                'days_until_due' => $daysUntilDue,
                                'days_overdue' => $daysOverdue,
                            ],
                        ]);

                        $installment->forceFill([
                            'reminder_count' => $installment->reminder_count + 1,
                            'last_reminded_at' => now(),
                        ])->save();

                        $created++;
                    }
                }
            });

        return $created;
    }

    public function agingBuckets(): array
    {
        $installments = PolicyInstallment::query()
            ->with('policy.client')
            ->whereIn('status', ['pending', 'overdue'])
            ->get();

        $buckets = [
            'current' => ['count' => 0, 'amount' => 0.0],
            '1_30' => ['count' => 0, 'amount' => 0.0],
            '31_60' => ['count' => 0, 'amount' => 0.0],
            '61_90' => ['count' => 0, 'amount' => 0.0],
            '90_plus' => ['count' => 0, 'amount' => 0.0],
        ];

        foreach ($installments as $installment) {
            $daysPastDue = now()->startOfDay()->diffInDays($installment->due_date->startOfDay(), false);
            $bucket = 'current';

            if ($daysPastDue < 0) {
                $bucket = 'current';
            } elseif ($daysPastDue <= 30) {
                $bucket = '1_30';
            } elseif ($daysPastDue <= 60) {
                $bucket = '31_60';
            } elseif ($daysPastDue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = '90_plus';
            }

            $buckets[$bucket]['count']++;
            $buckets[$bucket]['amount'] += (float) $installment->amount;
        }

        return $buckets;
    }

    public function activeRules(): array
    {
        return PaymentReminderRule::query()
            ->where('is_active', true)
            ->get()
            ->map(fn (PaymentReminderRule $rule) => $rule->config ?? [])
            ->values()
            ->all();
    }

    private function shouldSchedule(int $daysUntilDue, int $daysOverdue, int $offset): bool
    {
        if ($offset > 0) {
            return $daysUntilDue === $offset;
        }

        if ($offset === 0) {
            return $daysUntilDue === 0;
        }

        return $daysOverdue === abs($offset);
    }
}
