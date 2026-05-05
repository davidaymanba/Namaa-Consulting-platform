<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDelegation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DelegationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $delegations = UserDelegation::query()
            ->with(['delegator:id,name,role', 'delegate:id,name,role'])
            ->where('delegator_user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($delegations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'delegate_user_id' => ['required', 'exists:users,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $delegate = User::findOrFail($validated['delegate_user_id']);

        if ((int) $delegate->id === (int) $request->user()->id) {
            return response()->json(['message' => 'Cannot delegate to yourself.'], 422);
        }

        $delegation = UserDelegation::create([
            'delegator_user_id' => $request->user()->id,
            'delegate_user_id' => $delegate->id,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_active' => true,
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json(['message' => 'Delegation created.', 'data' => $delegation], 201);
    }

    public function destroy(Request $request, UserDelegation $userDelegation): JsonResponse
    {
        if ((int) $userDelegation->delegator_user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Unauthorized delegation access.'], 403);
        }

        $userDelegation->update(['is_active' => false]);

        return response()->json(['message' => 'Delegation deactivated.']);
    }
}
