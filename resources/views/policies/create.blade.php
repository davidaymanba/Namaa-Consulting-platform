<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-brand-900 dark:text-white">{{ __('messages.issue_car_policy') }}</h2>
    </x-slot>

    <form method="POST" action="{{ route('policies.store') }}" class="space-y-5">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div class="card">
                <h3 class="mb-3 text-lg font-bold">{{ __('messages.client_info') }}</h3>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm">{{ __('messages.client') }}</label>
                        <select name="client_id" class="w-full rounded-lg border-slate-300" required>
                            <option value="">--</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm">{{ __('messages.broker') }}</label>
                        <select name="broker_id" class="w-full rounded-lg border-slate-300">
                            <option value="">--</option>
                            @foreach ($brokers as $broker)
                                <option value="{{ $broker->id }}" @selected(old('broker_id') == $broker->id)>{{ $broker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-3 text-lg font-bold">{{ __('messages.coverage_details') }}</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm">{{ __('messages.start_date') }}</label><input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="w-full rounded-lg" required /></div>
                    <div><label class="mb-1 block text-sm">{{ __('messages.end_date') }}</label><input type="date" name="end_date" value="{{ old('end_date', now()->addYear()->toDateString()) }}" class="w-full rounded-lg" required /></div>
                    <div>
                        <label class="mb-1 block text-sm">{{ __('messages.insurance_type') }}</label>
                        <select name="insurance_type" class="w-full rounded-lg" required>
                            <option value="mandatory" @selected(old('insurance_type') === 'mandatory')>Mandatory</option>
                            <option value="comprehensive" @selected(old('insurance_type') === 'comprehensive')>Comprehensive</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">{{ __('messages.installment_mode') }}</label>
                        <select name="installment_mode" class="w-full rounded-lg" required>
                            <option value="monthly" @selected(old('installment_mode') === 'monthly')>{{ __('messages.monthly') }}</option>
                            <option value="quarterly" @selected(old('installment_mode') === 'quarterly')>{{ __('messages.quarterly') }}</option>
                            <option value="annual" @selected(old('installment_mode') === 'annual')>{{ __('messages.annual') }}</option>
                        </select>
                    </div>
                    <div><label class="mb-1 block text-sm">{{ __('messages.discount_pct') }}</label><input type="number" step="0.01" name="discount_pct" value="{{ old('discount_pct', 0) }}" class="w-full rounded-lg" /></div>
                    <div><label class="mb-1 block text-sm">{{ __('messages.loading_pct') }}</label><input type="number" step="0.01" name="loading_pct" value="{{ old('loading_pct', 0) }}" class="w-full rounded-lg" /></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="mb-3 text-lg font-bold">{{ __('messages.vehicle_data') }}</h3>
            <div class="grid gap-3 md:grid-cols-3">
                <div><label class="mb-1 block text-sm">{{ __('messages.plate_number') }}</label><input name="plate_number" value="{{ old('plate_number') }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.make') }}</label><input name="make" value="{{ old('make') }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.model') }}</label><input name="model" value="{{ old('model') }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.year') }}</label><input type="number" name="year" value="{{ old('year', 2021) }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.market_value') }}</label><input type="number" step="0.01" name="market_value" value="{{ old('market_value', 15000) }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.engine_cc') }}</label><input type="number" name="engine_cc" value="{{ old('engine_cc', 1600) }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.chassis_number') }}</label><input name="chassis_number" value="{{ old('chassis_number') }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.driver_name') }}</label><input name="driver_name" value="{{ old('driver_name') }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.driver_age') }}</label><input type="number" name="driver_age" value="{{ old('driver_age', 30) }}" class="w-full rounded-lg" required /></div>
                <div><label class="mb-1 block text-sm">{{ __('messages.license_type') }}</label><input name="license_type" value="{{ old('license_type', 'خصوصي') }}" class="w-full rounded-lg" required /></div>
            </div>
        </div>

        <div>
            <button class="rounded-lg bg-teal-600 px-5 py-2 font-bold text-white hover:bg-teal-700">{{ __('messages.issue_policy') }}</button>
        </div>
    </form>
</x-app-layout>
