<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ $policy->policy_no }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('policies.pdf', $policy) }}" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-bold text-white hover:bg-brand-700">PDF</a>
                <x-status-badge :status="$policy->status" />
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="card">
            <h3 class="mb-2 text-lg font-bold">{{ __('messages.policy_info') }}</h3>
            <p><span class="font-semibold">{{ __('messages.client') }}:</span> {{ $policy->client->name }}</p>
            <p><span class="font-semibold">{{ __('messages.broker') }}:</span> {{ $policy->broker?->name ?? '-' }}</p>
            <p><span class="font-semibold">{{ __('messages.premium') }}:</span> {{ number_format($policy->net_premium, 2) }} SAR</p>
            <p><span class="font-semibold">{{ __('messages.period') }}:</span> {{ $policy->start_date->format('Y-m-d') }} → {{ $policy->end_date->format('Y-m-d') }}</p>
            <p><span class="font-semibold">{{ __('messages.base_rate') }}:</span> {{ $policy->data['base_rate'] ?? '-' }}</p>
        </div>

        <div class="card">
            <h3 class="mb-2 text-lg font-bold">{{ __('messages.vehicle_data') }}</h3>
            <p><span class="font-semibold">{{ __('messages.plate_number') }}:</span> {{ $policy->data['plate_number'] ?? '-' }}</p>
            <p><span class="font-semibold">{{ __('messages.make') }} / {{ __('messages.model') }}:</span> {{ $policy->data['make'] ?? '-' }} {{ $policy->data['model'] ?? '' }}</p>
            <p><span class="font-semibold">{{ __('messages.market_value') }}:</span> {{ number_format($policy->data['market_value'] ?? 0, 2) }} SAR</p>
            <p><span class="font-semibold">{{ __('messages.driver_name') }}:</span> {{ $policy->data['driver_name'] ?? '-' }}</p>
        </div>

        <div class="card md:col-span-2">
            <h3 class="mb-2 text-lg font-bold">{{ __('messages.installments') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="border-b"><th class="px-2 py-1">#</th><th class="px-2 py-1">{{ __('messages.due_date') }}</th><th class="px-2 py-1">{{ __('messages.amount') }}</th><th class="px-2 py-1">{{ __('messages.status') }}</th></tr></thead>
                    <tbody>
                        @foreach ($policy->installments as $ins)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-2 py-1">{{ $ins->sequence }}</td>
                                <td class="px-2 py-1">{{ $ins->due_date->format('Y-m-d') }}</td>
                                <td class="px-2 py-1">{{ number_format($ins->amount, 2) }}</td>
                                <td class="px-2 py-1"><x-status-badge :status="$ins->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
