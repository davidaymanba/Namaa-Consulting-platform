<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500/90 text-lg font-extrabold text-white shadow-lg shadow-brand-900/30">
                NI
            </div>
            <h1 class="text-2xl font-extrabold text-white">{{ __('Welcome Back') }}</h1>
            <p class="mt-1 text-sm text-brand-100/80">{{ __('Sign in to continue to your insurance workspace') }}</p>
        </div>

        <x-auth-session-status class="rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-200" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-brand-100/80">{{ __('Email') }}</label>
                <input
                    id="email"
                    class="w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-slate-300/70 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-400/50"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@namaa.sa"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-300" />
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-brand-100/80">{{ __('Password') }}</label>
                <input
                    id="password"
                    class="w-full rounded-xl border border-white/20 bg-white/10 px-3 py-2.5 text-sm text-white placeholder:text-slate-300/70 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-400/50"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-300" />
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-200">
                    <input id="remember_me" type="checkbox" class="rounded border-white/30 bg-white/10 text-brand-500 shadow-sm focus:ring-brand-400/70" name="remember">
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-brand-200 hover:text-white" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-brand-500 to-cyan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-900/30 transition hover:from-brand-400 hover:to-cyan-400">
                {{ __('Log in') }}
            </button>
        </form>

        <div class="rounded-xl border border-white/20 bg-white/5 px-3 py-3 text-xs text-slate-200">
            <p class="mb-1 font-bold text-brand-100">{{ __('Demo Accounts') }}</p>
            <p>admin@namaa.sa / Password@123</p>
            <p>claims@namaa.sa / Password@123</p>
        </div>
    </div>
</x-guest-layout>
