<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.policies') }}</h2>
            <a href="{{ route('policies.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">{{ __('messages.issue_car_policy') }}</a>
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-start text-slate-500 dark:border-slate-700">
                    <th class="px-3 py-2">{{ __('messages.policy_no') }}</th>
                    <th class="px-3 py-2">{{ __('messages.client') }}</th>
                    <th class="px-3 py-2">{{ __('messages.type') }}</th>
                    <th class="px-3 py-2">{{ __('messages.premium') }}</th>
                    <th class="px-3 py-2">{{ __('messages.period') }}</th>
                    <th class="px-3 py-2">{{ __('messages.status') }}</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($policies as $policy)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="px-3 py-2 font-semibold">{{ $policy->policy_no }}</td>
                        <td class="px-3 py-2">{{ $policy->client->name }}</td>
                        <td class="px-3 py-2">{{ config('insurance.insurance_types')[$policy->type] ?? $policy->type }}</td>
                        <td class="px-3 py-2">{{ number_format($policy->net_premium, 2) }} SAR</td>
                        <td class="px-3 py-2">{{ $policy->start_date->format('Y-m-d') }} → {{ $policy->end_date->format('Y-m-d') }}</td>
                        <td class="px-3 py-2"><x-status-badge :status="$policy->status" /></td>
                        <td class="px-3 py-2"><a href="{{ route('policies.show', $policy) }}" class="text-brand-600 hover:text-brand-800">{{ __('messages.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">{{ __('messages.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $policies->links() }}</div>
    </div>
</x-app-layout>
