<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.reinsurance') }}</h2>
            <a href="{{ route('reinsurance.export') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">Export Excel</a>
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="border-b"><th class="px-2 py-1">Policy</th><th class="px-2 py-1">Insured</th><th class="px-2 py-1">Type</th><th class="px-2 py-1">Premium</th><th class="px-2 py-1">RI Share</th><th class="px-2 py-1">Claims Recovered</th><th class="px-2 py-1">Reinsurer</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="px-2 py-1">{{ $row->policy?->policy_no }}</td>
                        <td class="px-2 py-1">{{ $row->policy?->client?->name }}</td>
                        <td class="px-2 py-1">{{ $row->policy?->type }}</td>
                        <td class="px-2 py-1">{{ number_format($row->policy_premium, 2) }}</td>
                        <td class="px-2 py-1">{{ number_format($row->ri_share_amount, 2) }}</td>
                        <td class="px-2 py-1">{{ number_format($row->claims_recovered, 2) }}</td>
                        <td class="px-2 py-1">{{ $row->treaty?->reinsurer }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-2 py-4 text-center text-slate-500">{{ __('messages.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $rows->links() }}</div>
    </div>
</x-app-layout>
