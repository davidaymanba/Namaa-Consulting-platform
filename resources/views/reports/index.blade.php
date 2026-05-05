<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.reports') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.pdf', request()->query()) }}" class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-bold text-white">PDF</a>
                <a href="{{ route('reports.excel', request()->query()) }}" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white">Excel</a>
            </div>
        </div>
    </x-slot>

    <form method="GET" class="card mb-4 grid gap-3 md:grid-cols-3">
        <div><label class="mb-1 block text-xs">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="w-full rounded-lg" /></div>
        <div><label class="mb-1 block text-xs">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="w-full rounded-lg" /></div>
        <div class="flex items-end"><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white">Filter</button></div>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="card"><p class="text-sm text-slate-500">Premiums</p><p class="mt-2 text-2xl font-bold">{{ number_format($premiumsTotal, 2) }} SAR</p></div>
        <div class="card"><p class="text-sm text-slate-500">Approved Claims</p><p class="mt-2 text-2xl font-bold">{{ number_format($claimsTotal, 2) }} SAR</p></div>
        <div class="card"><p class="text-sm text-slate-500">Loss Ratio</p><p class="mt-2 text-2xl font-bold">{{ number_format($lossRatio, 2) }}%</p></div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="card overflow-x-auto">
            <h3 class="mb-2 text-lg font-bold">Production by Type</h3>
            <table class="min-w-full text-sm"><thead><tr class="border-b"><th class="px-2 py-1">Type</th><th class="px-2 py-1">Policies</th><th class="px-2 py-1">Premiums</th></tr></thead><tbody>@foreach ($production as $row)<tr class="border-b border-slate-100 dark:border-slate-800"><td class="px-2 py-1">{{ $row->type }}</td><td class="px-2 py-1">{{ $row->policies_count }}</td><td class="px-2 py-1">{{ number_format($row->premium_total, 2) }}</td></tr>@endforeach</tbody></table>
        </div>

        <div class="card overflow-x-auto">
            <h3 class="mb-2 text-lg font-bold">Claims by Status</h3>
            <table class="min-w-full text-sm"><thead><tr class="border-b"><th class="px-2 py-1">Status</th><th class="px-2 py-1">Claims</th><th class="px-2 py-1">Approved Amount</th></tr></thead><tbody>@foreach ($claims as $row)<tr class="border-b border-slate-100 dark:border-slate-800"><td class="px-2 py-1">{{ $row->status }}</td><td class="px-2 py-1">{{ $row->claims_count }}</td><td class="px-2 py-1">{{ number_format($row->approved_total, 2) }}</td></tr>@endforeach</tbody></table>
        </div>

        <div class="card overflow-x-auto lg:col-span-2">
            <h3 class="mb-2 text-lg font-bold">Broker Performance</h3>
            <table class="min-w-full text-sm"><thead><tr class="border-b"><th class="px-2 py-1">Broker</th><th class="px-2 py-1">Production</th><th class="px-2 py-1">Premiums</th></tr></thead><tbody>@foreach ($brokers as $broker)<tr class="border-b border-slate-100 dark:border-slate-800"><td class="px-2 py-1">{{ $broker->name }}</td><td class="px-2 py-1">{{ $broker->production_count }}</td><td class="px-2 py-1">{{ number_format($broker->premium_total ?? 0, 2) }}</td></tr>@endforeach</tbody></table>
        </div>
    </div>
</x-app-layout>
