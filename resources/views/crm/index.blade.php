<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.crm') }}</h2>
    </x-slot>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="card overflow-x-auto">
            <h3 class="mb-3 text-lg font-bold">Clients</h3>
            <table class="min-w-full text-sm">
                <thead><tr class="border-b"><th class="px-2 py-1">Name</th><th class="px-2 py-1">Type</th><th class="px-2 py-1">Policies</th><th class="px-2 py-1">Claims</th></tr></thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr class="border-b border-slate-100 dark:border-slate-800"><td class="px-2 py-1">{{ $client->name }}</td><td class="px-2 py-1">{{ $client->type }}</td><td class="px-2 py-1">{{ $client->policies_count }}</td><td class="px-2 py-1">{{ $client->claims_count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">{{ $clients->links() }}</div>
        </div>

        <div class="card overflow-x-auto">
            <h3 class="mb-3 text-lg font-bold">Brokers</h3>
            <table class="min-w-full text-sm">
                <thead><tr class="border-b"><th class="px-2 py-1">Code</th><th class="px-2 py-1">Name</th><th class="px-2 py-1">Contact</th><th class="px-2 py-1">Policies</th></tr></thead>
                <tbody>
                    @foreach ($brokers as $broker)
                        <tr class="border-b border-slate-100 dark:border-slate-800"><td class="px-2 py-1">{{ $broker->code }}</td><td class="px-2 py-1">{{ $broker->name }}</td><td class="px-2 py-1">{{ $broker->contact }}</td><td class="px-2 py-1">{{ $broker->policies_count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">{{ $brokers->links() }}</div>
        </div>
    </div>
</x-app-layout>
