<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.finance') }}</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="card"><p class="text-sm text-slate-500">Premium Income</p><p class="mt-2 text-2xl font-bold">{{ number_format($income, 2) }} SAR</p></div>
            <div class="card"><p class="text-sm text-slate-500">Claims Expense</p><p class="mt-2 text-2xl font-bold">{{ number_format($claimsExpense, 2) }} SAR</p></div>
            <div class="card"><p class="text-sm text-slate-500">Cash Flow (Net)</p><p class="mt-2 text-2xl font-bold">{{ number_format($cashMovement, 2) }} SAR</p></div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="card">
                <h3 class="mb-2 text-lg font-bold">Top Ledger Accounts</h3>
                <ul class="space-y-1 text-sm">
                    @foreach ($balances as $row)
                        <li class="flex justify-between border-b border-slate-100 py-1 dark:border-slate-800"><span>{{ $row->account }}</span><span>{{ number_format($row->total, 2) }}</span></li>
                    @endforeach
                </ul>
            </div>

            <div class="card overflow-x-auto">
                <h3 class="mb-2 text-lg font-bold">Latest Journal Entries</h3>
                <table class="min-w-full text-sm">
                    <thead><tr class="border-b"><th class="px-2 py-1">Date</th><th class="px-2 py-1">DR</th><th class="px-2 py-1">CR</th><th class="px-2 py-1">Amount</th><th class="px-2 py-1">Currency</th></tr></thead>
                    <tbody>
                    @foreach ($entries as $entry)
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="px-2 py-1">{{ $entry->date->format('Y-m-d') }}</td>
                            <td class="px-2 py-1">{{ $entry->debit_account }}</td>
                            <td class="px-2 py-1">{{ $entry->credit_account }}</td>
                            <td class="px-2 py-1">{{ number_format($entry->amount, 2) }}</td>
                            <td class="px-2 py-1">{{ $entry->currency }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
