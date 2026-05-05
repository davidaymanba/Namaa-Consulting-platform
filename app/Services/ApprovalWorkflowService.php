<?php

namespace App\Services;

use App\Models\ApprovalFlowRule;
use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalWorkflowService
{
    public function createRequest(
        string $module,
        int $recordId,
        string $requestType,
        int $requestedBy,
        ?string $reason = null,
        ?float $amount = null,
        array $context = [],
    ): ApprovalRequest {
        $rule = $this->resolveRule($module, $requestType, $amount);

        return ApprovalRequest::create([
            'module' => $module,
            'record_id' => $recordId,
            'request_type' => $requestType,
            'required_role' => $rule?->required_role ?? 'BRANCH_MANAGER',
            'current_stage' => 1,
            'total_stages' => $rule?->total_stages ?? 1,
            'sla_hours' => $rule?->sla_hours ?? 24,
            'due_at' => now()->addHours($rule?->sla_hours ?? 24),
            'requested_by' => $requestedBy,
            'reason' => $reason,
            'context' => $context,
        ]);
    }

    public function actionableFor(User $user)
    {
        $delegatedRoles = app(DelegationService::class)->activeDelegatedRolesFor($user->id);
        $allowedRoles = array_values(array_unique(array_merge([$user->role], $delegatedRoles)));

        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereIn('required_role', $allowedRoles)
            ->where('requested_by', '!=', $user->id);
    }

    public function canActOn(ApprovalRequest $request, User $user): bool
    {
        $delegatedRoles = app(DelegationService::class)->activeDelegatedRolesFor($user->id);
        $allowedRoles = array_values(array_unique(array_merge([$user->role], $delegatedRoles)));

        return in_array($request->required_role, $allowedRoles, true);
    }

    public function approve(ApprovalRequest $request, User $actor): ApprovalRequest
    {
        if ($request->status !== 'pending') {
            return $request;
        }

        if ($request->current_stage < $request->total_stages) {
            $request->update([
                'current_stage' => $request->current_stage + 1,
                'due_at' => now()->addHours($request->sla_hours),
            ]);

            return $request->fresh();
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ]);

        return $request->fresh();
    }

    public function reject(ApprovalRequest $request, User $actor, ?string $reason = null): ApprovalRequest
    {
        $context = $request->context ?? [];
        if ($reason) {
            $context['rejection_reason'] = $reason;
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'context' => $context,
        ]);

        return $request->fresh();
    }

    public function markSlaBreaches(): int
    {
        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNull('escalated_at')
            ->update(['escalated_at' => now()]);
    }

    private function resolveRule(string $module, string $requestType, ?float $amount): ?ApprovalFlowRule
    {
        $query = ApprovalFlowRule::query()
            ->where('module', $module)
            ->where('request_type', $requestType)
            ->where('is_active', true)
            ->orderBy('min_amount');

        if ($amount === null) {
            return $query->first();
        }

        return $query
            ->where(function ($q) use ($amount) {
                $q->whereNull('min_amount')->orWhere('min_amount', '<=', $amount);
            })
            ->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            })
            ->first();
    }
}
