@props(['status'])

@php
    $map = [
        'active' => 'bg-emerald-100 text-emerald-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'cancelled' => 'bg-rose-100 text-rose-700',
        'expired' => 'bg-slate-200 text-slate-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-rose-100 text-rose-700',
        'paid' => 'bg-cyan-100 text-cyan-700',
        'registered' => 'bg-amber-100 text-amber-700',
        'under_investigation' => 'bg-violet-100 text-violet-700',
        'surveyor_assigned' => 'bg-sky-100 text-sky-700',
        'report_received' => 'bg-indigo-100 text-indigo-700',
        'failed' => 'bg-rose-100 text-rose-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
    ];

    $class = $map[strtolower($status)] ?? 'bg-slate-100 text-slate-700';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-1 text-xs font-bold '.$class]) }}>
    {{ strtoupper($status) }}
</span>
