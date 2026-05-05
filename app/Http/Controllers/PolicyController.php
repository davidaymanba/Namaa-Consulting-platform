<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Client;
use App\Models\Policy;
use App\Models\PolicyInstallment;
use App\Models\Transaction;
use App\Services\AccountingService;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditService;
use App\Services\PremiumCalculatorService;
use App\Services\ReinsuranceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PolicyController extends Controller
{
    public function __construct(
        private readonly PremiumCalculatorService $premiumCalculator,
        private readonly AccountingService $accounting,
        private readonly ReinsuranceService $reinsurance,
        private readonly ApprovalWorkflowService $approvalWorkflow,
        private readonly AuditService $audit,
    ) {
    }

    public function index(): View
    {
        $query = Policy::query()->with(['client', 'broker'])->latest();

        if (auth()->user()?->role === 'BRANCH_MANAGER') {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        if (auth()->user()?->role === 'BROKER') {
            $query->where('broker_id', data_get(auth()->user()->permissions, 'broker_id', -1));
        }

        if (auth()->user()?->role === 'CLIENT') {
            $query->where('client_id', data_get(auth()->user()->permissions, 'client_id', -1));
        }

        $policies = $query->paginate(20);

        return view('policies.index', compact('policies'));
    }

    public function create(): View
    {
        return view('policies.create', [
            'clients' => Client::query()->orderBy('name')->get(),
            'brokers' => Broker::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'broker_id' => ['nullable', 'exists:brokers,id'],
            'plate_number' => ['required', 'string'],
            'make' => ['required', 'string'],
            'model' => ['required', 'string'],
            'year' => ['required', 'integer', 'min:1990'],
            'market_value' => ['required', 'numeric', 'min:1'],
            'engine_cc' => ['required', 'integer', 'min:600'],
            'chassis_number' => ['required', 'string'],
            'driver_name' => ['required', 'string'],
            'driver_age' => ['required', 'integer', 'min:18'],
            'license_type' => ['required', 'string'],
            'insurance_type' => ['required', 'in:mandatory,comprehensive'],
            'discount_pct' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'loading_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'installment_mode' => ['required', 'in:monthly,quarterly,annual'],
        ]);

        $premium = $this->premiumCalculator->forCar($validated);

        if ($premium['discount_pct'] > 15) {
            $this->approvalWorkflow->createRequest(
                module: 'policies',
                recordId: 0,
                requestType: 'premium_discount',
                requestedBy: (int) auth()->id(),
                reason: 'Discount above 15%',
                amount: (float) $premium['net_premium'],
                context: ['discount_pct' => $premium['discount_pct']]
            );
        }

        $policy = DB::transaction(function () use ($validated, $premium) {
            $policy = Policy::create([
                'policy_no' => 'CAR-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'client_id' => $validated['client_id'],
                'broker_id' => $validated['broker_id'] ?? null,
                'branch_id' => auth()->user()?->branch_id,
                'issued_by' => auth()->id(),
                'type' => 'car',
                'status' => 'active',
                'premium' => $premium['premium'],
                'discount_pct' => $premium['discount_pct'],
                'loading_pct' => $premium['loading_pct'],
                'net_premium' => $premium['net_premium'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'issued_at' => now(),
                'data' => [
                    'plate_number' => $validated['plate_number'],
                    'make' => $validated['make'],
                    'model' => $validated['model'],
                    'year' => $validated['year'],
                    'market_value' => $validated['market_value'],
                    'engine_cc' => $validated['engine_cc'],
                    'chassis_number' => $validated['chassis_number'],
                    'driver_name' => $validated['driver_name'],
                    'driver_age' => $validated['driver_age'],
                    'license_type' => $validated['license_type'],
                    'insurance_type' => $validated['insurance_type'],
                    'installment_mode' => $validated['installment_mode'],
                    'base_rate' => $premium['base_rate'],
                ],
            ]);

            foreach ($this->premiumCalculator->generateInstallments((float) $premium['net_premium'], $validated['installment_mode'], $validated['start_date']) as $item) {
                PolicyInstallment::create([
                    'policy_id' => $policy->id,
                    'sequence' => $item['sequence'],
                    'due_date' => $item['due_date'],
                    'amount' => $item['amount'],
                    'status' => 'pending',
                ]);
            }

            Transaction::create([
                'transaction_no' => 'TXN-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'policy_id' => $policy->id,
                'type' => 'policy_issue',
                'status' => 'completed',
                'amount' => $policy->net_premium,
                'currency' => 'SAR',
                'transaction_date' => now(),
            ]);

            return $policy;
        });

        $this->accounting->onPolicyIssued($policy->policy_no, (float) $policy->net_premium);
        $this->reinsurance->distributeForPolicy($policy);
        $this->audit->log('create', 'policies', $policy, [], $policy->toArray());

        return redirect()->route('policies.show', $policy)->with('success', __('messages.policy_issued'));
    }

    public function show(Policy $policy): View
    {
        if (auth()->user()?->role === 'BRANCH_MANAGER' && $policy->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

        if (auth()->user()?->role === 'BROKER' && $policy->broker_id !== (int) data_get(auth()->user()->permissions, 'broker_id', -1)) {
            abort(403);
        }

        if (auth()->user()?->role === 'CLIENT' && $policy->client_id !== (int) data_get(auth()->user()->permissions, 'client_id', -1)) {
            abort(403);
        }

        $policy->load(['client', 'broker', 'installments', 'claims']);

        return view('policies.show', compact('policy'));
    }

    public function exportPdf(Policy $policy): Response|BinaryFileResponse
    {
        if (auth()->user()?->role === 'BRANCH_MANAGER' && $policy->branch_id !== auth()->user()->branch_id) {
            abort(403);
        }

        if (auth()->user()?->role === 'BROKER' && $policy->broker_id !== (int) data_get(auth()->user()->permissions, 'broker_id', -1)) {
            abort(403);
        }

        if (auth()->user()?->role === 'CLIENT' && $policy->client_id !== (int) data_get(auth()->user()->permissions, 'client_id', -1)) {
            abort(403);
        }

        $policy->load(['client', 'broker', 'installments']);
        $pdf = Pdf::loadView('policies.pdf', compact('policy'));

        return $pdf->download($policy->policy_no.'.pdf');
    }

    public function edit(Policy $policy): View
    {
        return view('policies.edit', compact('policy'));
    }

    public function update(Request $request, Policy $policy): RedirectResponse
    {
        $old = $policy->toArray();
        $policy->update($request->only(['status', 'end_date']));
        $this->audit->log('update', 'policies', $policy, $old, $policy->fresh()->toArray());

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function destroy(Policy $policy): RedirectResponse
    {
        $this->approvalWorkflow->createRequest(
            module: 'policies',
            recordId: $policy->id,
            requestType: 'cancellation',
            requestedBy: (int) auth()->id(),
            reason: 'Policy cancellation request',
            amount: (float) $policy->net_premium,
            context: ['policy_no' => $policy->policy_no]
        );

        $old = $policy->toArray();
        $policy->update(['status' => 'cancelled']);
        $this->audit->log('cancel', 'policies', $policy, $old, $policy->toArray());

        return redirect()->route('policies.index')->with('success', __('messages.cancellation_pending_approval'));
    }
}
