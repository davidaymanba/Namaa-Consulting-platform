<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\Policy;
use App\Models\Transaction;
use App\Services\AccountingService;
use App\Services\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function __construct(
        private readonly AccountingService $accounting,
        private readonly AuditService $audit,
    ) {
    }

    public function index(): View
    {
        $query = Claim::query()->with('policy.client')->latest();

        if (auth()->user()?->role === 'BRANCH_MANAGER') {
            $query->whereHas('policy', fn ($q) => $q->where('branch_id', auth()->user()->branch_id));
        }

        if (auth()->user()?->role === 'BROKER') {
            $query->whereHas('policy', fn ($q) => $q->where('broker_id', data_get(auth()->user()->permissions, 'broker_id', -1)));
        }

        if (auth()->user()?->role === 'CLIENT') {
            $query->whereHas('policy', fn ($q) => $q->where('client_id', data_get(auth()->user()->permissions, 'client_id', -1)));
        }

        $claims = $query->paginate(20);

        return view('claims.index', compact('claims'));
    }

    public function create(): View
    {
        return view('claims.create', [
            'policies' => Policy::query()->where('status', 'active')->orderBy('policy_no')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'policy_id' => ['required', 'exists:policies,id'],
            'incident_date' => ['required', 'date'],
            'report_date' => ['required', 'date', 'after_or_equal:incident_date'],
            'description' => ['required', 'string', 'max:2000'],
            'claimed_amount' => ['required', 'numeric', 'min:1'],
            'estimated_loss' => ['required', 'numeric', 'min:0'],
            'documents.*' => ['file', 'max:5120'],
        ]);

        $claim = Claim::create([
            'claim_no' => 'CLM-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'policy_id' => $validated['policy_id'],
            'status' => 'registered',
            'incident_date' => $validated['incident_date'],
            'report_date' => $validated['report_date'],
            'description' => $validated['description'],
            'claimed_amount' => $validated['claimed_amount'],
            'estimated_loss' => $validated['estimated_loss'],
            'is_large_claim' => (float) $validated['claimed_amount'] > 10000,
            'escalated_at' => (float) $validated['claimed_amount'] > 10000 ? now() : null,
            'last_status_update_at' => now(),
            'workflow_notes' => [['status' => 'registered', 'at' => now()->toDateTimeString(), 'by' => auth()->id()]],
        ]);

        if ($claim->is_large_claim) {
            ApprovalRequest::create([
                'module' => 'claims',
                'record_id' => $claim->id,
                'request_type' => 'large_claim_escalation',
                'required_role' => 'BRANCH_MANAGER',
                'requested_by' => auth()->id(),
                'reason' => 'Claim exceeds 10,000 SAR',
            ]);
        }

        foreach ($request->file('documents', []) as $doc) {
            $path = $doc->store('claims', 'public');
            ClaimDocument::create([
                'claim_id' => $claim->id,
                'file_name' => $doc->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $doc->getClientMimeType(),
                'size' => $doc->getSize(),
            ]);
        }

        $this->audit->log('create', 'claims', $claim, [], $claim->toArray());

        return redirect()->route('claims.show', $claim)->with('success', __('messages.claim_registered'));
    }

    public function show(Claim $claim): View
    {
        if (
            auth()->user()?->role === 'BRANCH_MANAGER' &&
            $claim->policy()->value('branch_id') !== auth()->user()->branch_id
        ) {
            abort(403);
        }

        if (
            auth()->user()?->role === 'BROKER' &&
            (int) $claim->policy()->value('broker_id') !== (int) data_get(auth()->user()->permissions, 'broker_id', -1)
        ) {
            abort(403);
        }

        if (
            auth()->user()?->role === 'CLIENT' &&
            (int) $claim->policy()->value('client_id') !== (int) data_get(auth()->user()->permissions, 'client_id', -1)
        ) {
            abort(403);
        }

        $claim->load(['policy.client', 'documents']);

        return view('claims.show', compact('claim'));
    }

    public function edit(Claim $claim): View
    {
        return view('claims.edit', compact('claim'));
    }

    public function update(Request $request, Claim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:registered,under_investigation,surveyor_assigned,report_received,approved,rejected,paid'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'workflow_note' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $claim->toArray();
        $notes = $claim->workflow_notes ?? [];
        $notes[] = [
            'status' => $validated['status'],
            'note' => $validated['workflow_note'] ?? null,
            'at' => now()->toDateTimeString(),
            'by' => auth()->id(),
        ];

        $claim->update([
            'status' => $validated['status'],
            'approved_amount' => $validated['approved_amount'] ?? $claim->approved_amount,
            'paid_amount' => $validated['paid_amount'] ?? $claim->paid_amount,
            'last_status_update_at' => now(),
            'workflow_notes' => $notes,
        ]);

        if ((float) ($validated['approved_amount'] ?? 0) > 5000) {
            ApprovalRequest::create([
                'module' => 'claims',
                'record_id' => $claim->id,
                'request_type' => 'senior_claim_approval',
                'required_role' => 'BRANCH_MANAGER',
                'requested_by' => auth()->id(),
                'reason' => 'Approved amount exceeds 5,000 SAR',
            ]);
        }

        if ($validated['status'] === 'paid' && ! empty($validated['paid_amount'])) {
            Transaction::create([
                'transaction_no' => 'TXN-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'claim_id' => $claim->id,
                'policy_id' => $claim->policy_id,
                'type' => 'claim_payment',
                'status' => 'completed',
                'amount' => $validated['paid_amount'],
                'currency' => 'SAR',
                'transaction_date' => now(),
            ]);

            $this->accounting->onClaimPaid($claim->claim_no, (float) $validated['paid_amount']);
        }

        $this->audit->log('update', 'claims', $claim, $old, $claim->fresh()->toArray());

        return back()->with('success', __('messages.saved_successfully'));
    }

    public function destroy(Claim $claim): RedirectResponse
    {
        $old = $claim->toArray();
        $claim->delete();
        $this->audit->log('delete', 'claims', $claim, $old, []);

        return redirect()->route('claims.index')->with('success', __('messages.saved_successfully'));
    }
}
