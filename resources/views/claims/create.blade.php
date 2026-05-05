<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.register_claim') }}</h2>
    </x-slot>

    <form method="POST" enctype="multipart/form-data" action="{{ route('claims.store') }}" class="space-y-4">
        @csrf

        <div class="card space-y-3">
            <div>
                <label class="mb-1 block text-sm">{{ __('messages.policy_no') }}</label>
                <select name="policy_id" class="w-full rounded-lg" required>
                    <option value="">--</option>
                    @foreach ($policies as $policy)
                        <option value="{{ $policy->id }}" @selected(old('policy_id') == $policy->id)>{{ $policy->policy_no }} - {{ $policy->client->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div><label class="mb-1 block text-sm">{{ __('messages.incident_date') }}</label><input type="date" name="incident_date" value="{{ old('incident_date', now()->toDateString()) }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.report_date') }}</label><input type="date" name="report_date" value="{{ old('report_date', now()->toDateString()) }}" class="w-full rounded-lg" required /></div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div><label class="mb-1 block text-sm">{{ __('messages.claimed_amount') }}</label><input type="number" step="0.01" name="claimed_amount" value="{{ old('claimed_amount', 1000) }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.estimated_loss') }}</label><input type="number" step="0.01" name="estimated_loss" value="{{ old('estimated_loss', 800) }}" class="w-full rounded-lg" required /></div>
            </div>

            <div>
                <label class="mb-1 block text-sm">{{ __('messages.description') }}</label>
                <textarea name="description" rows="4" class="w-full rounded-lg" required>{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm">{{ __('messages.documents') }}</label>
                <input type="file" name="documents[]" multiple class="w-full rounded-lg" />
            </div>
        </div>

        <button class="rounded-lg bg-teal-600 px-5 py-2 font-bold text-white hover:bg-teal-700">{{ __('messages.save') }}</button>
    </form>
</x-app-layout>
