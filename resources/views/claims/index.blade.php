<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.claims') }}</h2>
            <a href="{{ route('claims.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">{{ __('messages.register_claim') }}</a>
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-start text-slate-500 dark:border-slate-700">
                    <th class="px-3 py-2">{{ __('messages.claim_no') }}</th>
                    <th class="px-3 py-2">{{ __('messages.policy_no') }}</th>
                    <th class="px-3 py-2">{{ __('messages.client') }}</th>
                    <th class="px-3 py-2">{{ __('messages.claimed_amount') }}</th>
                    <th class="px-3 py-2">{{ __('messages.status') }}</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($claims as $claim)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="px-3 py-2 font-semibold">{{ $claim->claim_no }}</td>
                        <td class="px-3 py-2">{{ $claim->policy->policy_no }}</td>
                        <td class="px-3 py-2">{{ $claim->policy->client->name }}</td>
                        <td class="px-3 py-2">{{ number_format($claim->claimed_amount, 2) }} SAR</td>
                        <td class="px-3 py-2"><x-status-badge :status="$claim->status" /></td>
                        <td class="px-3 py-2 space-x-1">
                            @if ($claim->last_status_update_at && $claim->last_status_update_at->lt(now()->subDays(30)))
                                <span class="rounded bg-amber-100 px-2 py-1 text-xs font-bold text-amber-700">{{ __('messages.stale_claim_alert') }}</span>
                            @endif
                            <a href="{{ route('claims.show', $claim) }}" class="text-brand-600">{{ __('messages.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('messages.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $claims->links() }}</div>
    </div>
</x-app-layout>
