<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Namaa Insurance Platform') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        x-data="{
            dark: localStorage.getItem('theme') === 'dark',
            navOpen: false,
            toast: '{{ session('success') ?? session('error') }}',
            toastType: '{{ session('error') ? 'error' : 'success' }}'
        }"
        x-init="document.documentElement.classList.toggle('dark', dark)"
        class="min-h-screen bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100"
    >
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-20 -left-16 h-72 w-72 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-700/30"></div>
            <div class="absolute top-1/3 -right-16 h-80 w-80 rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-700/20"></div>
            <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.7),rgba(241,245,249,0.88))] dark:bg-[linear-gradient(to_bottom,rgba(2,6,23,0.85),rgba(2,6,23,0.96))]"></div>
        </div>

        <div class="min-h-screen lg:flex">
            @include('layouts.navigation')

            <div class="flex-1 lg:ps-2">
                <header class="sticky top-0 z-20 border-b border-white/70 bg-white/75 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-900/65">
                    <div class="mx-auto flex max-w-screen-2xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <div class="hidden h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-sm font-extrabold text-white shadow sm:flex">NI</div>
                            <div>
                                <h1 class="text-base font-extrabold text-brand-900 dark:text-white sm:text-lg">{{ __('messages.company_name') }}</h1>
                                <p class="text-xs text-slate-500 dark:text-slate-300">{{ __('messages.core_system') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', dark)" class="btn-soft">
                                <span x-text="dark ? '{{ __('messages.light_mode') }}' : '{{ __('messages.dark_mode') }}'"></span>
                            </button>

                            <form method="POST" action="{{ route('locale.switch') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                                <button class="btn-soft">
                                    {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
                                </button>
                            </form>

                            <a href="{{ route('profile.edit') }}" class="btn-soft hidden sm:inline-flex">{{ auth()->user()->name }}</a>
                        </div>
                    </div>
                </header>

                @isset($header)
                    <div class="mx-auto max-w-screen-2xl px-4 pt-6 sm:px-6 lg:px-8">
                        <div class="glass-panel p-4 sm:p-5">
                            {{ $header }}
                        </div>
                    </div>
                @endisset

                <main class="mx-auto max-w-screen-2xl p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <div x-show="toast" x-transition class="fixed bottom-4 start-4 z-50 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-lg" :class="toastType === 'error' ? 'bg-red-600' : 'bg-emerald-600'">
            <span x-text="toast"></span>
        </div>
    </body>
</html>
