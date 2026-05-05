<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Client;
use Illuminate\Contracts\View\View;

class CRMController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $clientsQuery = Client::query()->withCount(['policies', 'claims'])->latest();
        $brokersQuery = Broker::query()->withCount('policies')->latest();

        if ($user?->role === 'BROKER') {
            $brokerId = (int) data_get($user->permissions, 'broker_id', -1);
            $clientsQuery->whereHas('policies', fn ($q) => $q->where('broker_id', $brokerId));
            $brokersQuery->where('id', $brokerId);
        }

        if ($user?->role === 'BRANCH_MANAGER') {
            $branchId = $user->branch_id;
            $clientsQuery->whereHas('policies', fn ($q) => $q->where('branch_id', $branchId));
            $brokersQuery->whereHas('policies', fn ($q) => $q->where('branch_id', $branchId));
        }

        $clients = $clientsQuery->paginate(10, ['*'], 'clients_page');
        $brokers = $brokersQuery->paginate(10, ['*'], 'brokers_page');

        return view('crm.index', compact('clients', 'brokers'));
    }
}
