<x-app-layout>
    @php
        $kpis = $kpis ?? [
            'total_active_policies' => 0,
            'monthly_premium' => 0,
            'open_claims_count' => 0,
            'loss_ratio' => 0,
            'renewal_alerts' => 0,
            'top_broker' => null,
        ];
        $premiumByType = $premiumByType ?? [];
        $policyDistribution = $policyDistribution ?? [];
        $trend = $trend ?? [];
        $latestTransactions = $latestTransactions ?? collect();
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.dashboard') }}</h2>
            <span class="text-sm text-brand-600 dark:text-brand-200">{{ now()->format('Y-m-d H:i') }}</span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="card"><p class="text-sm text-slate-500">{{ __('messages.total_active_policies') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($kpis['total_active_policies']) }}</p></div>
            <div class="card"><p class="text-sm text-slate-500">{{ __('messages.monthly_premium') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($kpis['monthly_premium'], 2) }} SAR</p></div>
            <div class="card"><p class="text-sm text-slate-500">{{ __('messages.open_claims') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($kpis['open_claims_count']) }}</p></div>
            <div class="card"><p class="text-sm text-slate-500">{{ __('messages.loss_ratio') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($kpis['loss_ratio'], 2) }}%</p></div>
            <div class="card"><p class="text-sm text-slate-500">{{ __('messages.renewal_alerts') }}</p><p class="mt-2 text-3xl font-extrabold">{{ number_format($kpis['renewal_alerts']) }}</p></div>
            <div class="card"><p class="text-sm text-slate-500">{{ __('messages.top_broker') }}</p><p class="mt-2 text-xl font-extrabold">{{ $kpis['top_broker']->name ?? '-' }}</p><p class="text-sm text-slate-500">{{ number_format($kpis['top_broker']->total_premium ?? 0, 2) }} SAR</p></div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="card">
                <h3 class="mb-4 text-lg font-bold">{{ __('messages.monthly_premium_by_type') }}</h3>
                <div id="premiumByTypeChart"></div>
            </div>
            <div class="card">
                <h3 class="mb-4 text-lg font-bold">{{ __('messages.claims_vs_premiums') }}</h3>
                <div id="claimsPremiumsChart"></div>
            </div>
            <div class="card lg:col-span-2">
                <h3 class="mb-4 text-lg font-bold">{{ __('messages.policy_distribution') }}</h3>
                <div id="policyDistributionChart"></div>
            </div>
        </section>

        <section class="card">
            <h3 class="mb-4 text-lg font-bold">{{ __('messages.latest_transactions') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-start text-slate-500 dark:border-slate-700">
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">{{ __('messages.transaction_type') }}</th>
                            <th class="px-3 py-2">{{ __('messages.amount') }}</th>
                            <th class="px-3 py-2">{{ __('messages.status') }}</th>
                            <th class="px-3 py-2">{{ __('messages.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestTransactions as $txn)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-3 py-2 font-semibold">{{ $txn->transaction_no }}</td>
                                <td class="px-3 py-2">{{ $txn->type }}</td>
                                <td class="px-3 py-2">{{ number_format($txn->amount, 2) }} {{ $txn->currency }}</td>
                                <td class="px-3 py-2"><x-status-badge :status="$txn->status" /></td>
                                <td class="px-3 py-2">{{ $txn->transaction_date?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">{{ __('messages.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        const typesMap = @json(config('insurance.insurance_types'));
        const premiumByType = @json($premiumByType);
        const policyDistribution = @json($policyDistribution);
        const trend = @json($trend);

        new ApexCharts(document.querySelector('#premiumByTypeChart'), {
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
            colors: ['#1d7ff4'],
            series: [{ name: 'Premium', data: Object.keys(typesMap).map(type => premiumByType[type] ?? 0) }],
            xaxis: { categories: Object.values(typesMap) },
        }).render();

        new ApexCharts(document.querySelector('#claimsPremiumsChart'), {
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            colors: ['#10b981', '#ef4444'],
            stroke: { width: 3 },
            series: [
                { name: 'Premiums', data: trend.map(item => item.premiums) },
                { name: 'Claims', data: trend.map(item => item.claims) },
            ],
            xaxis: { categories: trend.map(item => item.month) },
        }).render();

        new ApexCharts(document.querySelector('#policyDistributionChart'), {
            chart: { type: 'pie', height: 340 },
            labels: Object.keys(typesMap).map(type => typesMap[type]),
            series: Object.keys(typesMap).map(type => policyDistribution[type] ?? 0),
            colors: ['#1d7ff4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
        }).render();
    </script>
</x-app-layout>
