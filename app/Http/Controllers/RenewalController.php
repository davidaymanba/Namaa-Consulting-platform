<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Services\RenewalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RenewalController extends Controller
{
    public function __construct(private readonly RenewalService $renewalService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'candidates' => $this->renewalService->candidates()->map(fn (Policy $policy) => [
                'id' => $policy->id,
                'policy_no' => $policy->policy_no,
                'client_name' => $policy->client?->name,
                'type' => $policy->type,
                'end_date' => $policy->end_date?->toDateString(),
                'suggested_quote' => $this->renewalService->suggestPrice($policy),
            ]),
        ]);
    }

    public function suggest(Request $request, Policy $policy): JsonResponse
    {
        return response()->json([
            'policy_id' => $policy->id,
            'policy_no' => $policy->policy_no,
            'suggested_quote' => $this->renewalService->suggestPrice($policy),
        ]);
    }

    public function bulkRenew(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'policy_ids' => ['required', 'array', 'min:1'],
            'policy_ids.*' => ['integer', 'exists:policies,id'],
        ]);

        $renewed = $this->renewalService->bulkRenew($validated['policy_ids'], (int) $request->user()->id);

        return response()->json([
            'message' => 'Renewals processed.',
            'data' => $renewed->map(fn (Policy $policy) => [
                'id' => $policy->id,
                'policy_no' => $policy->policy_no,
                'renewed_from_policy_id' => $policy->renewed_from_policy_id,
            ]),
        ]);
    }

    public function renew(Request $request, Policy $policy): JsonResponse
    {
        $renewed = $this->renewalService->renew($policy, (int) $request->user()->id);

        return response()->json([
            'message' => 'Policy renewed.',
            'data' => [
                'id' => $renewed->id,
                'policy_no' => $renewed->policy_no,
                'renewed_from_policy_id' => $renewed->renewed_from_policy_id,
            ],
        ], 201);
    }
}
