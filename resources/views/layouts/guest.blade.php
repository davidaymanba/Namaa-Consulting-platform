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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-950 font-tajawal text-slate-100 antialiased">
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(29,127,244,0.26),transparent_45%)]"></div>
            <div class="absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl"></div>
            <div class="relative flex min-h-screen items-center justify-center p-6">
                <div class="w-full max-w-md rounded-3xl border border-brand-700/50 bg-slate-900/85 p-7 shadow-2xl backdrop-blur-lg">
                    <div class="mb-6 text-center text-brand-100">
                        <a href="/" class="text-xl font-extrabold">Namaa Insurance</a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
