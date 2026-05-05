<?php

namespace App\Services;

use App\Models\UserDelegation;
use Illuminate\Support\Facades\DB;

class DelegationService
{
    public function activeDelegatorIdsFor(int $delegateUserId): array
    {
        return UserDelegation::query()
            ->where('delegate_user_id', $delegateUserId)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->pluck('delegator_user_id')
            ->all();
    }

    public function activeDelegatedRolesFor(int $delegateUserId): array
    {
        return DB::table('user_delegations')
            ->join('users as delegators', 'delegators.id', '=', 'user_delegations.delegator_user_id')
            ->where('user_delegations.delegate_user_id', $delegateUserId)
            ->where('user_delegations.is_active', true)
            ->where('user_delegations.starts_at', '<=', now())
            ->where('user_delegations.ends_at', '>=', now())
            ->pluck('delegators.role')
            ->unique()
            ->values()
            ->all();
    }
}
