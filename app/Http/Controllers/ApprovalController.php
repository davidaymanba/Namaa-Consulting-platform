<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalWorkflowService $workflow)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $approvals = $this->workflow
            ->actionableFor($request->user())
            ->latest()
            ->paginate(20);

        return response()->json($approvals);
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'Request is already processed.'], 422);
        }

        if (! $this->workflow->canActOn($approvalRequest, $request->user())) {
            return response()->json(['message' => 'Unauthorized approver role.'], 403);
        }

        $updated = $this->workflow->approve($approvalRequest, $request->user());

        return response()->json(['message' => 'Approval processed.', 'data' => $updated]);
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        if ($approvalRequest->status !== 'pending') {
            return response()->json(['message' => 'Request is already processed.'], 422);
        }

        if (! $this->workflow->canActOn($approvalRequest, $request->user())) {
            return response()->json(['message' => 'Unauthorized approver role.'], 403);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $updated = $this->workflow->reject($approvalRequest, $request->user(), $validated['reason'] ?? null);

        return response()->json(['message' => 'Approval rejected.', 'data' => $updated]);
    }
}
