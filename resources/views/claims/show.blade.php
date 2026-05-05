<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ $claim->claim_no }}</h2>
            <x-status-badge :status="$claim->status" />
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="card">
            <h3 class="mb-2 text-lg font-bold">{{ __('messages.claim_details') }}</h3>
            <p><span class="font-semibold">{{ __('messages.policy_no') }}:</span> {{ $claim->policy->policy_no }}</p>
            <p><span class="font-semibold">{{ __('messages.client') }}:</span> {{ $claim->policy->client->name }}</p>
            <p><span class="font-semibold">{{ __('messages.claimed_amount') }}:</span> {{ number_format($claim->claimed_amount, 2) }} SAR</p>
            <p><span class="font-semibold">{{ __('messages.approved_amount') }}:</span> {{ number_format($claim->approved_amount, 2) }} SAR</p>
            <p><span class="font-semibold">{{ __('messages.paid_amount') }}:</span> {{ number_format($claim->paid_amount, 2) }} SAR</p>
            @if ($claim->is_large_claim)
                <p class="mt-2 rounded bg-red-100 px-2 py-1 text-sm font-bold text-red-700">{{ __('messages.large_claim_escalated') }}</p>
            @endif
        </div>

        <div class="card">
            <h3 class="mb-2 text-lg font-bold">{{ __('messages.update_workflow') }}</h3>
            <form method="POST" action="{{ route('claims.update', $claim) }}" class="space-y-2">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm">{{ __('messages.status') }}</label>
                    <select name="status" class="w-full rounded-lg">
                        @foreach (config('insurance.claim_statuses') as $status)
                            <option value="{{ $status }}" @selected($claim->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm">{{ __('messages.approved_amount') }}</label><input type="number" step="0.01" name="approved_amount" class="w-full rounded-lg" value="{{ old('approved_amount', $claim->approved_amount) }}" /></div>
                    <div><label class="mb-1 block text-sm">{{ __('messages.paid_amount') }}</label><input type="number" step="0.01" name="paid_amount" class="w-full rounded-lg" value="{{ old('paid_amount', $claim->paid_amount) }}" /></div>
                </div>
                <div><label class="mb-1 block text-sm">{{ __('messages.note') }}</label><input name="workflow_note" class="w-full rounded-lg" /></div>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-white">{{ __('messages.save') }}</button>
            </form>
        </div>

        <div class="card md:col-span-2">
            <h3 class="mb-2 text-lg font-bold">{{ __('messages.documents') }}</h3>
            <ul class="space-y-1 text-sm">
                @forelse ($claim->documents as $doc)
                    <li><a href="{{ asset('storage/'.$doc->file_path) }}" class="text-brand-600" target="_blank">{{ $doc->file_name }}</a></li>
                @empty
                    <li class="text-slate-500">{{ __('messages.no_data') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
